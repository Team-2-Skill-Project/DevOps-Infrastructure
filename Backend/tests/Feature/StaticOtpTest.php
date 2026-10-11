<?php

use App\Mail\EmailOtpMail;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/** @return array<string, string> */
function staticOtpRegistrationPayload(): array
{
    return [
        'name' => 'Static OTP User',
        'email' => 'static-otp@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];
}

function enableStaticOtp(string $code = '123456'): void
{
    app()->detectEnvironment(fn (): string => 'staging');

    config()->set([
        'app.env' => 'staging',
        'otp.static_enabled' => true,
        'otp.static_code' => $code,
    ]);
}

afterEach(function (): void {
    app()->detectEnvironment(fn (): string => 'testing');

    config()->set([
        'app.env' => 'testing',
        'otp.static_enabled' => false,
        'otp.static_code' => null,
    ]);
});

test('staging static OTP verifies registration without sending email and remains single-use', function () {
    $code = '123456';
    enableStaticOtp($code);
    $this->freezeTime();
    Mail::fake();

    $this->postJson('/api/auth/register', staticOtpRegistrationPayload())
        ->assertCreated()
        ->assertJsonMissingPath('otp');

    $otp = EmailOtp::sole();
    expect(Hash::check($code, $otp->code_hash))->toBeTrue()
        ->and($otp->expires_at->timestamp)->toBe(now()->addMinutes(10)->timestamp);
    Mail::assertNothingSent();

    $this->postJson('/api/auth/verify-email-otp', [
        'email' => 'static-otp@example.com',
        'otp' => $code,
    ])->assertOk()->assertJsonMissingPath('otp');

    expect(User::sole()->email_verified_at)->not->toBeNull();
    $this->assertDatabaseCount('email_otps', 0);

    $this->postJson('/api/auth/verify-email-otp', [
        'email' => 'static-otp@example.com',
        'otp' => $code,
    ])->assertUnprocessable();
});

test('static OTP resend preserves cooldown and replaces the stored code without email', function () {
    enableStaticOtp();
    $this->freezeTime();
    Mail::fake();

    $this->postJson('/api/auth/register', staticOtpRegistrationPayload())->assertCreated();
    $oldOtp = EmailOtp::sole();

    $this->postJson('/api/auth/resend-email-otp', ['email' => 'static-otp@example.com'])
        ->assertTooManyRequests()
        ->assertHeader('Retry-After');
    $this->assertModelExists($oldOtp);
    Mail::assertNothingSent();

    $this->travel(61)->seconds();
    $this->postJson('/api/auth/resend-email-otp', ['email' => 'static-otp@example.com'])
        ->assertOk();

    $this->assertModelMissing($oldOtp);
    $newOtp = EmailOtp::sole();
    expect(Hash::check('123456', $newOtp->code_hash))->toBeTrue()
        ->and($newOtp->attempts)->toBe(0);
    Mail::assertNothingSent();
});

test('static OTP preserves resend rate limits', function () {
    enableStaticOtp();
    $this->freezeTime();
    $user = User::factory()->unverified()->create(['email' => 'static-rate-limit@example.com']);
    Mail::fake();

    foreach (range(1, 3) as $index) {
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.'.($index + 1)])
            ->postJson('/api/auth/resend-email-otp', ['email' => $user->email])
            ->assertOk();
        $this->travel(61)->seconds();
    }

    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.5'])
        ->postJson('/api/auth/resend-email-otp', ['email' => $user->email])
        ->assertTooManyRequests();

    Mail::assertNothingSent();
});

test('static OTP verifies forgot-password requests without sending email', function () {
    enableStaticOtp();
    $user = User::factory()->unverified()->create(['email' => 'static-reset@example.com']);
    Mail::fake();

    $this->postJson('/api/auth/forgot-password', ['email' => $user->email])
        ->assertOk()
        ->assertJsonMissingPath('otp')
        ->assertJsonMissingPath('reset_token');

    $otp = EmailOtp::where('purpose', EmailOtp::PASSWORD_RESET)->sole();
    expect(Hash::check('123456', $otp->code_hash))->toBeTrue();
    Mail::assertNothingSent();

    $this->postJson('/api/auth/forgot-password/verify-otp', [
        'email' => $user->email,
        'otp' => '123456',
    ])->assertOk()
        ->assertJsonPath('expires_in', 600)
        ->assertJsonMissingPath('otp');

    expect($otp->fresh()->reset_token_hash)->not->toBeNull();
});

test('static OTP still expires before verification', function () {
    enableStaticOtp();
    $this->freezeTime();
    $user = User::factory()->unverified()->create(['email' => 'static-expiry@example.com']);
    Mail::fake();

    $this->postJson('/api/auth/forgot-password', ['email' => $user->email])->assertOk();
    $this->travel(10)->minutes();

    $this->postJson('/api/auth/forgot-password/verify-otp', [
        'email' => $user->email,
        'otp' => '123456',
    ])->assertUnprocessable()->assertJsonValidationErrors('otp');

    expect(EmailOtp::sole()->reset_token_hash)->toBeNull();
    Mail::assertNothingSent();
});

test('static OTP keeps the five-attempt limit', function () {
    enableStaticOtp();
    $user = User::factory()->unverified()->create(['email' => 'static-attempts@example.com']);
    Mail::fake();

    $this->postJson('/api/auth/forgot-password', ['email' => $user->email])->assertOk();
    $otp = EmailOtp::sole();

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/auth/forgot-password/verify-otp', [
            'email' => $user->email,
            'otp' => '654321',
        ])->assertUnprocessable()->assertJsonValidationErrors('otp');

        expect($otp->fresh()->attempts)->toBe($attempt);
    }

    $this->postJson('/api/auth/forgot-password/verify-otp', [
        'email' => $user->email,
        'otp' => '123456',
    ])->assertTooManyRequests();

    expect($otp->fresh()->reset_token_hash)->toBeNull();
    Mail::assertNothingSent();
});

test('production ignores static OTP mode and uses the normal email flow', function () {
    app()->detectEnvironment(fn (): string => 'production');

    config()->set([
        'app.env' => 'production',
        'otp.static_enabled' => true,
        'otp.static_code' => '000000',
    ]);
    Mail::fake();

    $this->postJson('/api/auth/register', [
        ...staticOtpRegistrationPayload(),
        'email' => 'production-otp@example.com',
    ])->assertCreated()->assertJsonMissingPath('otp');

    $mail = Mail::sent(EmailOtpMail::class)->sole();
    $otp = EmailOtp::sole();
    expect($mail->code)->not->toBe('000000')
        ->and(Hash::check($mail->code, $otp->code_hash))->toBeTrue();

    $this->postJson('/api/auth/verify-email-otp', [
        'email' => 'production-otp@example.com',
        'otp' => '000000',
    ])->assertUnprocessable();

    $this->postJson('/api/auth/verify-email-otp', [
        'email' => 'production-otp@example.com',
        'otp' => $mail->code,
    ])->assertOk();
});

test('disabled static OTP mode keeps random email delivery as the default', function () {
    app()->detectEnvironment(fn (): string => 'local');

    config()->set([
        'app.env' => 'local',
        'otp.static_enabled' => false,
        'otp.static_code' => '000000',
    ]);
    Mail::fake();

    $this->postJson('/api/auth/register', staticOtpRegistrationPayload())->assertCreated();

    $mail = Mail::sent(EmailOtpMail::class)->sole();
    $otp = EmailOtp::sole();
    expect($mail->code)->not->toBe('000000')
        ->and(Hash::check($mail->code, $otp->code_hash))->toBeTrue();
});

test('invalid or missing staging static OTP configuration keeps normal email delivery', function (?string $code) {
    app()->detectEnvironment(fn (): string => 'staging');

    config()->set([
        'app.env' => 'staging',
        'otp.static_enabled' => true,
        'otp.static_code' => $code,
    ]);
    Mail::fake();

    $this->postJson('/api/auth/register', staticOtpRegistrationPayload())->assertCreated();

    $mail = Mail::sent(EmailOtpMail::class)->sole();
    $otp = EmailOtp::sole();
    expect($mail->code)->toMatch('/^[0-9]{6}$/')
        ->and(Hash::check($mail->code, $otp->code_hash))->toBeTrue();
})->with([
    'too short' => '12345',
    'non numeric' => 'abcdef',
    'missing' => null,
]);
