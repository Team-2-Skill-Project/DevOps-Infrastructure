<?php

use App\Mail\EmailOtpMail;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register without phone', function () {
    Mail::fake();
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticated();
    expect(User::sole()->email_verified_at)->toBeNull();
    $otp = EmailOtp::sole();
    expect($otp->user_id)->toBe(User::sole()->id);
    expect(Hash::check('password', User::sole()->password))->toBeTrue();
    Mail::assertSent(EmailOtpMail::class, fn (EmailOtpMail $mail) => $mail->hasTo('test@example.com')
        && $mail->purpose === EmailOtp::EMAIL_VERIFICATION && preg_match('/^[0-9]{6}$/', $mail->code)
        && Hash::check($mail->code, $otp->code_hash));
    $this->get(route('dashboard'))->assertRedirect(route('verification.notice'));
    Mail::assertSentCount(1);
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'phone' => null,
    ]);

    $this->postJson('/api/auth/verify-email-otp', [
        'email' => 'test@example.com',
        'otp' => Mail::sent(EmailOtpMail::class)->sole()->code,
    ])->assertOk();

    $this->assertDatabaseCount('email_otps', 0);
    expect(User::sole()->email_verified_at)->not->toBeNull();
    $this->actingAs(User::sole())->get(route('dashboard'))->assertOk();
});

test('registration event sends email OTP when the action has not sent one', function () {
    $user = User::factory()->unverified()->create(['phone' => null]);
    Mail::fake();

    event(new Registered($user));

    $otp = EmailOtp::sole();
    expect($otp->user_id)->toBe($user->id);
    Mail::assertSent(EmailOtpMail::class, fn (EmailOtpMail $mail) => $mail->hasTo($user->email)
        && Hash::check($mail->code, $otp->code_hash));
});

test('registration event does not send email OTP for verified users', function () {
    $user = User::factory()->create();
    Mail::fake();

    event(new Registered($user));

    $this->assertDatabaseCount('email_otps', 0);
    Mail::assertNothingSent();
});

test('registration ignores obsolete phone input', function (mixed $phone) {
    Mail::fake();
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => $phone,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();
    expect(User::sole()->phone)->toBeNull();
    Mail::assertSent(EmailOtpMail::class);
})->with([
    'empty' => [''],
    'null' => [null],
    'not a string' => [201012345678],
    'too long' => [str_repeat('1', 256)],
]);

test('registration preserves existing phone values and ignores duplicate phone input', function () {
    $existingUser = User::factory()->create(['phone' => '+20 10 1234 5678']);
    Mail::fake();

    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+20 10 1234 5678',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();
    expect($existingUser->fresh()->phone)->toBe('+20 10 1234 5678');
    $this->assertDatabaseHas('users', ['email' => 'test@example.com', 'phone' => null]);
    Mail::assertSent(EmailOtpMail::class);
});
