<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    config()->set('inertia.ssr.enabled', false);
    config()->set('services.google', ['client_id' => 'test-client', 'client_secret' => 'test-secret', 'redirect' => 'http://localhost/auth/google/callback']);
    Http::preventStrayRequests();
});

test('trusted Google login creates a verified user without phone onboarding or OTP mail', function () {
    Mail::fake();
    Http::fake([
        'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'test-token']),
        'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
            'sub' => 'google-123', 'email' => 'google@example.com', 'name' => 'Google User', 'email_verified' => true,
        ]),
    ]);

    $this->withSession(['google_oauth' => ['state' => 'test-state', 'expires_at' => now()->addMinutes(10)->timestamp]])
        ->get(route('google.callback', ['state' => 'test-state', 'code' => 'test-code']))->assertRedirect(route('dashboard'));

    $user = User::sole();
    $this->assertAuthenticatedAs($user);
    expect($user->email_verified_at)->not->toBeNull();
    expect($user->password)->toBeNull();
    Mail::assertNothingSent();
    $this->get(route('dashboard'))->assertOk();
    Http::assertSentCount(2);
});

test('Google links a matching existing account and preserves its password', function () {
    $user = User::factory()->unverified()->create(['email' => 'google@example.com']);
    Mail::fake();
    Http::fake([
        'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'test-token']),
        'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
            'sub' => 'google-123', 'email' => 'Google@example.com', 'email_verified' => true,
        ]),
    ]);

    $this->withSession(['google_oauth' => ['state' => 'test-state', 'expires_at' => now()->addMinutes(10)->timestamp]])
        ->get(route('google.callback', ['state' => 'test-state', 'code' => 'test-code']))->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseCount('users', 1);
    expect($user->fresh()->google_id)->toBe('google-123');
    expect($user->fresh()->email_verified_at)->not->toBeNull();
    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
    Mail::assertNothingSent();
    Http::assertSentCount(2);
});

test('Google rejects unverified email identities', function () {
    Mail::fake();
    Http::fake([
        'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'test-token']),
        'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
            'sub' => 'google-123', 'email' => 'google@example.com', 'email_verified' => false,
        ]),
    ]);

    $this->withSession(['google_oauth' => ['state' => 'test-state', 'expires_at' => now()->addMinutes(10)->timestamp]])
        ->get(route('google.callback', ['state' => 'test-state', 'code' => 'test-code']))->assertRedirect(route('login'))->assertSessionHasErrors('google');

    $this->assertGuest();
    $this->assertDatabaseCount('users', 0);
    Mail::assertNothingSent();
    Http::assertSentCount(2);
});

test('Google rejects invalid state before contacting the provider', function () {
    $this->get(route('google.callback', ['state' => 'invalid', 'code' => 'test-code']))
        ->assertRedirect(route('login'))->assertSessionHasErrors('google');

    $this->assertGuest();
    Http::assertNothingSent();
});
