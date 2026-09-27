<?php

use App\Mail\EmailOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::emailVerification());
});

test('sends verification notification', function () {
    Mail::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect(route('home'));

    Mail::assertSent(EmailOtpMail::class, fn (EmailOtpMail $mail) => $mail->hasTo($user->email));
});

test('does not send verification notification if email is verified', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect(route('dashboard', absolute: false));

    Mail::assertNothingSent();
});
