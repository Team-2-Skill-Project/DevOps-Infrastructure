<?php

use App\Mail\EmailOtpMail;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;

/** @return array<string, string> */
function otpRegistrationPayload(): array
{
    return ['name' => 'OTP Test User', 'email' => 'otp@example.com',
        'password' => 'password', 'password_confirmation' => 'password'];
}

test('registration without phone sends a hashed expiring email OTP', function () {
    $this->freezeSecond();
    Mail::fake();

    $this->postJson('/api/auth/register', otpRegistrationPayload())->assertCreated()
        ->assertJsonPath('email', 'otp@example.com')
        ->assertJsonMissingPath('user.password')->assertJsonMissingPath('otp');

    $user = User::sole();
    expect($user->phone)->toBeNull();
    expect($user->email_verified_at)->toBeNull();
    expect(Hash::check('password', $user->password))->toBeTrue();
    $otp = EmailOtp::sole();
    expect($otp->expires_at->equalTo(now()->addMinutes(10)))->toBeTrue();
    Mail::assertSent(EmailOtpMail::class, fn (EmailOtpMail $mail) => $mail->hasTo($user->email)
        && $mail->purpose === EmailOtp::EMAIL_VERIFICATION && preg_match('/^[0-9]{6}$/', $mail->code)
        && Hash::check($mail->code, $otp->code_hash));
    $this->assertGuest();
});

test('delivered OTP verifies email once and allows normal login', function () {
    Mail::fake();
    $this->postJson('/api/auth/register', otpRegistrationPayload())->assertCreated();
    $code = Mail::sent(EmailOtpMail::class)->sole()->code;

    $this->postJson('/api/auth/verify-email-otp', ['email' => 'otp@example.com', 'otp' => $code])
        ->assertOk()->assertJsonPath('message', 'Email verified successfully.');

    expect(User::sole()->email_verified_at)->not->toBeNull();
    $this->assertDatabaseCount('email_otps', 0);
    $this->postJson('/api/auth/verify-email-otp', ['email' => 'otp@example.com', 'otp' => $code])->assertUnprocessable();
    $this->post(route('login.store'), ['email' => 'otp@example.com', 'password' => 'password'])
        ->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticated();
    Mail::assertSentCount(1);
});

test('delivery failure rolls back registration and returns 503', function () {
    Mail::shouldReceive('to')->once()->with('otp@example.com')->andThrow(new TransportException('Unavailable'));

    $this->postJson('/api/auth/register', otpRegistrationPayload())->assertServiceUnavailable();

    $this->assertDatabaseCount('users', 0);
    $this->assertDatabaseCount('email_otps', 0);
});

test('API registration ignores obsolete phone input', function (mixed $phone) {
    Mail::fake();

    $this->postJson('/api/auth/register', [...otpRegistrationPayload(), 'phone' => $phone])
        ->assertCreated();

    expect(User::sole()->phone)->toBeNull();
    Mail::assertSent(EmailOtpMail::class);
})->with(['+2001098765432', '+9660501234567', '+20abc1098765432', '123', ['not a string'], null]);

test('API registration preserves existing phone values and ignores duplicate phone input', function () {
    $existingUser = User::factory()->create(['phone' => '+201098765432']);
    Mail::fake();

    $this->postJson('/api/auth/register', [...otpRegistrationPayload(), 'phone' => '+201098765432'])
        ->assertCreated();

    expect($existingUser->fresh()->phone)->toBe('+201098765432');
    $this->assertDatabaseHas('users', ['email' => 'otp@example.com', 'phone' => null]);
    Mail::assertSent(EmailOtpMail::class);
});

test('email verification enforces expiry and attempts', function (bool $expired, int $attempts, string $code, int $status, int $expectedAttempts) {
    $this->freezeTime();
    $user = User::factory()->unverified()->create();
    $otp = EmailOtp::create(['user_id' => $user->id, 'email' => $user->email, 'purpose' => EmailOtp::EMAIL_VERIFICATION,
        'code_hash' => Hash::make('123456'), 'expires_at' => $expired ? now() : now()->addMinutes(10), 'attempts' => $attempts]);

    $this->postJson('/api/auth/verify-email-otp', ['email' => $user->email, 'otp' => $code])->assertStatus($status);

    expect($user->fresh()->email_verified_at)->toBeNull();
    expect($otp->fresh()->attempts)->toBe($expectedAttempts);
})->with(['expired' => [true, 0, '123456', 422, 0], 'limit' => [false, 5, '123456', 429, 5], 'wrong' => [false, 0, '654321', 422, 1]]);

test('resend replaces verification codes only after cooldown', function () {
    $this->freezeTime();
    Mail::fake();
    $this->postJson('/api/auth/register', otpRegistrationPayload())->assertCreated();
    $old = EmailOtp::sole();

    $this->postJson('/api/auth/resend-email-otp', ['email' => 'otp@example.com'])->assertTooManyRequests()->assertHeader('Retry-After');
    $this->assertModelExists($old);
    $this->travel(61)->seconds();
    $this->postJson('/api/auth/resend-email-otp', ['email' => 'otp@example.com'])->assertOk();

    $this->assertModelMissing($old);
    expect(EmailOtp::sole()->attempts)->toBe(0);
    Mail::assertSentCount(2);
});

test('verified accounts cannot resend email OTPs', function () {
    $user = User::factory()->create();
    Mail::fake();

    $this->postJson('/api/auth/resend-email-otp', ['email' => $user->email])->assertUnprocessable();

    $this->assertDatabaseCount('email_otps', 0);
    Mail::assertNothingSent();
});

test('OTP mail contains branding purpose code expiration and a sharing warning', function () {
    $mail = new EmailOtpMail('123456', EmailOtp::EMAIL_VERIFICATION, now()->addMinutes(10));

    $mail->assertSeeInHtml('SkillMatch');
    $mail->assertSeeInHtml('Verify your email address');
    $mail->assertSeeInHtml('123456');
    $mail->assertSeeInHtml('10 minutes');
    $mail->assertSeeInHtml('Do not share this code');
});

test('five incorrect verification codes lock out even the correct code', function () {
    $user = User::factory()->unverified()->create();
    $otp = EmailOtp::create(['user_id' => $user->id, 'email' => $user->email, 'purpose' => EmailOtp::EMAIL_VERIFICATION,
        'code_hash' => Hash::make('123456'), 'expires_at' => now()->addMinutes(10), 'attempts' => 0]);

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/auth/verify-email-otp', ['email' => $user->email, 'otp' => '654321'])->assertUnprocessable();
    }
    $this->postJson('/api/auth/verify-email-otp', ['email' => $user->email, 'otp' => '123456'])->assertTooManyRequests();

    expect($otp->fresh()->attempts)->toBe(5);
    expect($user->fresh()->email_verified_at)->toBeNull();
});

test('verification resend failures preserve the previous code', function () {
    $user = User::factory()->unverified()->create();
    $otp = EmailOtp::create(['user_id' => $user->id, 'email' => $user->email, 'purpose' => EmailOtp::EMAIL_VERIFICATION,
        'code_hash' => Hash::make('123456'), 'expires_at' => now()->addMinutes(10), 'last_sent_at' => now()->subMinutes(2)]);
    Mail::shouldReceive('to')->once()->with($user->email)->andThrow(new TransportException('Unavailable'));

    $this->postJson('/api/auth/resend-email-otp', ['email' => $user->email])->assertServiceUnavailable();

    $this->assertModelExists($otp);
    $this->assertDatabaseCount('email_otps', 1);
});

test('verification resends are rate limited across IPs and email casing', function () {
    $this->freezeTime();
    $user = User::factory()->unverified()->create(['email' => 'verify@example.com']);
    Mail::fake();

    foreach (range(1, 3) as $index) {
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.'.$index])
            ->postJson('/api/auth/resend-email-otp', ['email' => $user->email])->assertOk();
        $this->travel(61)->seconds();
    }
    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.4'])
        ->postJson('/api/auth/resend-email-otp', ['email' => 'VERIFY@example.com'])->assertTooManyRequests();

    Mail::assertSentCount(3);
});

test('codes issued to a previous email cannot verify a changed address', function () {
    $user = User::factory()->unverified()->create();
    $otp = EmailOtp::create(['user_id' => $user->id, 'email' => 'old@example.com', 'purpose' => EmailOtp::EMAIL_VERIFICATION,
        'code_hash' => Hash::make('123456'), 'expires_at' => now()->addMinutes(10)]);

    $this->postJson('/api/auth/verify-email-otp', ['email' => $user->email, 'otp' => '123456'])
        ->assertUnprocessable()->assertJsonValidationErrors('otp');

    $this->assertModelExists($otp);
    expect($user->fresh()->email_verified_at)->toBeNull();
});

test('verification rejects malformed input', function (array $payload, string $field) {
    $this->postJson('/api/auth/verify-email-otp', $payload)->assertUnprocessable()->assertJsonValidationErrors($field);
})->with([
    [['email' => 'invalid', 'otp' => '123456'], 'email'],
    [['email' => 'user@example.com', 'otp' => '12345'], 'otp'],
    [['email' => 'user@example.com', 'otp' => 'abcdef'], 'otp'],
]);
