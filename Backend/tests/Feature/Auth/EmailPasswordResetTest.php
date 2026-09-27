<?php

use App\Mail\EmailOtpMail;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia as Assert;
use Symfony\Component\Mailer\Exception\TransportException;

test('the existing Fortify forgot-password route serves the recovery page with password rules', function () {
    config()->set('inertia.ssr.enabled', false);

    $this->get(route('password.request'))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('auth/forgot-password')
            ->where('passwordRules', fn ($rules) => is_string($rules)));
});

/** @param array<string, mixed> $attributes */
function passwordResetOtp(User $user, array $attributes = []): EmailOtp
{
    return EmailOtp::create([
        'user_id' => $user->id,
        'email' => $user->email,
        'purpose' => EmailOtp::PASSWORD_RESET,
        'code_hash' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(10),
        'attempts' => 0,
        'last_sent_at' => now()->subMinute(),
        ...$attributes,
    ]);
}

/** @return array<string, string> */
function newEmailPassword(string $email = 'reset@example.com', string $token = ''): array
{
    return [
        'email' => $email,
        'reset_token' => $token ?: str_repeat('r', 64),
        'password' => 'Changed-Password-928!',
        'password_confirmation' => 'Changed-Password-928!',
    ];
}

test('Email recovery verifies an OTP then consumes a separate token without logging in', function (string $email, string $canonical) {
    $this->freezeSecond();
    $user = User::factory()->unverified()->create(['email' => $canonical]);
    $registrationOtp = passwordResetOtp($user, ['purpose' => EmailOtp::EMAIL_VERIFICATION]);
    $emailToken = Password::broker(config('fortify.passwords'))->createToken($user);
    Event::fake([PasswordReset::class]);
    Mail::fake();

    $this->postJson('/api/auth/forgot-password', ['email' => $email])->assertOk()
        ->assertJsonMissingPath('otp')->assertJsonMissingPath('debug_otp')->assertJsonMissingPath('reset_token');

    $otp = EmailOtp::where('purpose', EmailOtp::PASSWORD_RESET)->sole();
    $code = Mail::sent(EmailOtpMail::class)->sole()->code;
    expect(Hash::check($code, $otp->code_hash))->toBeTrue();
    expect($otp->expires_at->equalTo(now()->addMinutes(10)))->toBeTrue();
    expect($otp->last_sent_at->equalTo(now()))->toBeTrue();
    expect($otp->attempts)->toBe(0);
    Mail::assertSent(EmailOtpMail::class, fn (EmailOtpMail $mail) => $mail->hasTo($canonical) && $mail->purpose === EmailOtp::PASSWORD_RESET);

    $verified = $this->postJson('/api/auth/forgot-password/verify-otp', ['email' => $email, 'otp' => $code])
        ->assertOk()->assertJsonPath('expires_in', 600)->assertHeader('Cache-Control', 'no-store, private');
    $token = $verified->json('reset_token');
    expect($token)->toHaveLength(64)->not->toBe($code);
    expect(Hash::check($token, $otp->fresh()->reset_token_hash))->toBeTrue();
    expect($otp->fresh()->toArray())->not->toHaveKeys(['code_hash', 'reset_token_hash']);
    expect($otp->fresh()->reset_token_expires_at->equalTo(now()->addMinutes(10)))->toBeTrue();
    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
    $this->assertGuest();

    $this->postJson('/api/auth/reset-password', newEmailPassword($email, $token))->assertOk();

    expect(Hash::check('Changed-Password-928!', $user->fresh()->password))->toBeTrue();
    expect(Hash::check('password', $user->fresh()->password))->toBeFalse();
    expect(Password::broker(config('fortify.passwords'))->tokenExists($user, $emailToken))->toBeFalse();
    expect($user->fresh()->email_verified_at)->toBeNull();
    $this->assertModelMissing($otp);
    $this->assertModelExists($registrationOtp);
    $this->assertGuest();
    Event::assertDispatched(PasswordReset::class, fn (PasswordReset $event) => $event->user->is($user));

    $this->postJson('/api/auth/reset-password', newEmailPassword($email, $token))->assertUnprocessable();
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Changed-Password-928!'])
        ->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);
})->with([
    'lowercase' => ['reset@example.com', 'reset@example.com'],
    'mixed case' => ['OTHER@example.com', 'other@example.com'],
]);

test('unknown emails receive a generic 422 validation response without sending a code', function () {
    Mail::fake();

    $this->postJson('/api/auth/forgot-password', ['email' => 'reset@example.com'])
        ->assertUnprocessable()->assertJsonValidationErrors('email')
        ->assertJsonPath('message', 'Unable to send a reset code. Check the email address and try again.');

    $this->assertDatabaseCount('email_otps', 0);
    Mail::assertNothingSent();
});

test('invalid emails receive 422 without sending a code', function (mixed $email) {
    Mail::fake();

    $this->postJson('/api/auth/forgot-password', ['email' => $email])->assertUnprocessable()->assertJsonValidationErrors('email');

    $this->assertDatabaseCount('email_otps', 0);
    Mail::assertNothingSent();
})->with([null, ['array'], 'not-a-email', 'missing-domain@', str_repeat('1', 65)]);

test('a successful resend replaces reset OTP and token state while preserving registration OTPs', function () {
    $user = User::factory()->unverified()->create(['email' => 'reset@example.com']);
    $registrationOtp = passwordResetOtp($user, ['purpose' => EmailOtp::EMAIL_VERIFICATION]);
    $previous = passwordResetOtp($user, ['reset_token_hash' => Hash::make(str_repeat('r', 64)), 'reset_token_expires_at' => now()->addMinutes(10)]);
    Mail::fake();

    $this->postJson('/api/auth/forgot-password', ['email' => 'reset@example.com'])->assertOk();

    $this->assertModelMissing($previous);
    $this->assertModelExists($registrationOtp);
    $replacement = EmailOtp::where('purpose', EmailOtp::PASSWORD_RESET)->sole();
    expect($replacement->reset_token_hash)->toBeNull();
    expect($replacement->attempts)->toBe(0);
    Mail::assertSentCount(1);
    $this->postJson('/api/auth/reset-password', newEmailPassword())->assertUnprocessable();
    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

test('failed delivery returns 503 and restores previous reset state', function () {
    $user = User::factory()->unverified()->create(['email' => 'reset@example.com']);
    $previous = passwordResetOtp($user);
    Mail::shouldReceive('to')->once()->with($user->email)->andThrow(new TransportException('Delivery failed'));

    $this->postJson('/api/auth/forgot-password', ['email' => $user->email])->assertServiceUnavailable()
        ->assertExactJson(['message' => 'Unable to send the email code. Please try again shortly.']);

    $this->assertModelExists($previous);
    $this->assertDatabaseCount('email_otps', 1);
});

test('resending within one minute returns 429 even with different email formatting', function () {
    $user = User::factory()->unverified()->create(['email' => 'reset@example.com']);
    $previous = passwordResetOtp($user, ['last_sent_at' => now()]);
    Mail::fake();

    $this->postJson('/api/auth/forgot-password', ['email' => 'RESET@example.com'])->assertTooManyRequests()->assertHeader('Retry-After');

    $this->assertModelExists($previous);
    Mail::assertNothingSent();
});

test('OTP requests are limited per normalized email across IP addresses', function () {
    User::factory()->unverified()->create(['email' => 'reset@example.com']);
    Mail::fake();

    foreach (['RESET@example.com', 'reset@example.com', 'Reset@example.com'] as $index => $email) {
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.'.($index + 1)])
            ->postJson('/api/auth/forgot-password', ['email' => $email])->assertOk();
        $this->travel(61)->seconds();
    }
    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.4'])
        ->postJson('/api/auth/forgot-password', ['email' => 'reset@example.com'])->assertTooManyRequests();

    Mail::assertSentCount(3);
});

test('OTP request IP throttling limits enumeration across different emails', function () {
    Mail::fake();
    foreach (range(10, 19) as $suffix) {
        $this->postJson('/api/auth/forgot-password', ['email' => 'user'.$suffix.'@example.com'])->assertUnprocessable();
    }

    $this->postJson('/api/auth/forgot-password', ['email' => 'another@example.com'])->assertTooManyRequests();

    Mail::assertNothingSent();
});

test('invalid OTP attempts persist and a correct code cannot bypass five failures', function () {
    $user = User::factory()->unverified()->create(['email' => 'reset@example.com']);
    $otp = passwordResetOtp($user);

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/auth/forgot-password/verify-otp', ['email' => $user->email, 'otp' => '654321'])
            ->assertUnprocessable()->assertJsonValidationErrors('otp');
        expect($otp->fresh()->attempts)->toBe($attempt);
    }
    $this->postJson('/api/auth/forgot-password/verify-otp', ['email' => $user->email, 'otp' => '123456'])->assertTooManyRequests();

    expect($otp->fresh()->reset_token_hash)->toBeNull();
    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

test('missing expired consumed or wrong-purpose OTP cannot authorize a reset', function (string $state) {
    $this->freezeSecond();
    $user = User::factory()->unverified()->create(['email' => 'reset@example.com']);
    if ($state !== 'missing') {
        passwordResetOtp($user, match ($state) {
            'expired' => ['expires_at' => now()],
            'consumed' => ['reset_token_hash' => Hash::make(str_repeat('r', 64)), 'reset_token_expires_at' => now()->addMinutes(10)],
            default => ['purpose' => EmailOtp::EMAIL_VERIFICATION],
        });
    }

    $this->postJson('/api/auth/forgot-password/verify-otp', ['email' => $user->email, 'otp' => '123456'])
        ->assertUnprocessable()->assertJsonValidationErrors('otp')->assertJsonMissingPath('reset_token');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
})->with(['missing', 'expired', 'consumed', 'registration']);

test('reset tokens must be verified unexpired and bound to the same email', function (string $state) {
    $this->freezeSecond();
    $user = User::factory()->unverified()->create(['email' => 'reset@example.com']);
    $otherUser = User::factory()->unverified()->create(['email' => 'other@example.com']);
    passwordResetOtp($state === 'other email' ? $otherUser : $user, match ($state) {
        'unverified' => [],
        'expired' => ['reset_token_hash' => Hash::make(str_repeat('r', 64)), 'reset_token_expires_at' => now()],
        'wrong purpose' => ['purpose' => EmailOtp::EMAIL_VERIFICATION, 'reset_token_hash' => Hash::make(str_repeat('r', 64)), 'reset_token_expires_at' => now()->addMinutes(10)],
        default => ['reset_token_hash' => Hash::make(str_repeat('r', 64)), 'reset_token_expires_at' => now()->addMinutes(10)],
    });

    $this->postJson('/api/auth/reset-password', newEmailPassword($user->email, $state === 'invalid' ? str_repeat('x', 64) : ''))
        ->assertUnprocessable()->assertJsonValidationErrors('reset_token');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
    expect(Hash::check('password', $otherUser->fresh()->password))->toBeTrue();
    $this->assertDatabaseCount('email_otps', 1);
    $this->assertGuest();
})->with(['unverified', 'expired', 'other email', 'invalid', 'wrong purpose']);

test('password validation rejects weak or unconfirmed passwords without consuming the reset token', function (array $changes) {
    $user = User::factory()->unverified()->create(['email' => 'reset@example.com']);
    $otp = passwordResetOtp($user, ['reset_token_hash' => Hash::make(str_repeat('r', 64)), 'reset_token_expires_at' => now()->addMinutes(10)]);

    $this->postJson('/api/auth/reset-password', [...newEmailPassword(), ...$changes])
        ->assertUnprocessable()->assertJsonValidationErrors('password');

    $this->assertModelExists($otp);
    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
})->with([
    [['password' => 'short', 'password_confirmation' => 'short']],
    [['password_confirmation' => 'different']],
]);

test('password reset OTPs cannot verify registration emails', function () {
    $user = User::factory()->unverified()->create(['email' => 'reset@example.com']);
    $otp = passwordResetOtp($user);

    $this->postJson('/api/auth/verify-email-otp', ['email' => $user->email, 'otp' => '123456'])->assertUnprocessable();

    expect($user->fresh()->email_verified_at)->toBeNull();
    $this->assertModelExists($otp);
});

test('registration verification still selects its OTP when a newer reset OTP exists', function () {
    $user = User::factory()->unverified()->create(['email' => 'reset@example.com']);
    $registration = passwordResetOtp($user, ['purpose' => EmailOtp::EMAIL_VERIFICATION]);
    $reset = passwordResetOtp($user, ['code_hash' => Hash::make('654321'), 'created_at' => now()->addSecond()]);

    $this->postJson('/api/auth/verify-email-otp', ['email' => $user->email, 'otp' => '123456'])->assertOk();

    expect($user->fresh()->email_verified_at)->not->toBeNull();
    $this->assertModelMissing($registration);
    $this->assertModelExists($reset);
});
