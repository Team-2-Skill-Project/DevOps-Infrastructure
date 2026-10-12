<?php

use App\Mail\EmailOtpMail;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function (): void {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

test('registration remains tokenless until email OTP verification succeeds', function () {
    Mail::fake();

    $registration = $this->postJson('/api/auth/register', [
        'name' => 'Token Test User',
        'email' => 'token-registration@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertCreated();

    $registration->assertJsonMissingPath('access_token')
        ->assertJsonMissingPath('refresh_token');

    $code = Mail::sent(EmailOtpMail::class)->sole()->code;

    $verification = $this->postJson('/api/auth/verify-email-otp', [
        'email' => 'token-registration@example.com',
        'otp' => $code,
    ])->assertOk()
        ->assertJsonStructure(['access_token', 'refresh_token', 'token_type', 'expires_in', 'user'])
        ->assertJsonPath('token_type', 'bearer');

    $refreshToken = RefreshToken::sole();
    expect($refreshToken->token_hash)
        ->toBe(hash('sha256', $verification->json('refresh_token')))
        ->not->toBe($verification->json('refresh_token'));
});

test('API login returns an access token and a refresh token for a verified active user', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['access_token', 'refresh_token', 'token_type', 'expires_in', 'user'])
        ->assertJsonPath('token_type', 'bearer')
        ->assertJsonPath('user.id', $user->id);

    expect($response->json('refresh_token'))->toHaveLength(128);
    $this->assertDatabaseCount('refresh_tokens', 1);
    $this->assertDatabaseMissing('refresh_tokens', ['token_hash' => $response->json('refresh_token')]);
});

test('API access tokens expire after 15 minutes', function () {
    $this->freezeSecond();
    $user = User::factory()->create();

    $accessToken = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()
        ->assertJsonPath('expires_in', 900)
        ->json('access_token');

    $this->travel(901)->seconds();
    Auth::forgetGuards();
    JWTAuth::unsetToken();

    $this->withToken($accessToken)->getJson('/api/auth/me')->assertUnauthorized();
});

test('a valid refresh token returns a new access token and rotates the refresh token', function () {
    $user = User::factory()->create();
    $login = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();
    $oldAccessToken = $login->json('access_token');
    $oldRefreshToken = $login->json('refresh_token');

    $response = $this->postJson('/api/auth/refresh', ['refresh_token' => $oldRefreshToken]);

    $response->assertOk()
        ->assertJsonStructure(['access_token', 'refresh_token', 'token_type', 'expires_in'])
        ->assertJsonPath('token_type', 'bearer');
    expect($response->json('access_token'))->not->toBe($oldAccessToken)
        ->and($response->json('refresh_token'))->not->toBe($oldRefreshToken);
    $this->assertDatabaseHas('refresh_tokens', [
        'token_hash' => hash('sha256', $oldRefreshToken),
    ]);
    expect(RefreshToken::query()->whereNotNull('revoked_at')->count())->toBe(1);
});

test('a rotated refresh token is rejected on replay', function () {
    $user = User::factory()->create();
    $oldRefreshToken = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()->json('refresh_token');

    $this->postJson('/api/auth/refresh', ['refresh_token' => $oldRefreshToken])->assertOk();

    $this->postJson('/api/auth/refresh', ['refresh_token' => $oldRefreshToken])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

test('an expired refresh token is rejected', function () {
    $user = User::factory()->create();
    $refreshToken = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()->json('refresh_token');
    RefreshToken::sole()->update(['expires_at' => now()->subMinute()]);

    $this->postJson('/api/auth/refresh', ['refresh_token' => $refreshToken])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

test('an invalid refresh token is rejected', function () {
    $this->postJson('/api/auth/refresh', ['refresh_token' => str_repeat('invalid', 20)])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

test('a missing refresh token returns a validation error', function () {
    $this->postJson('/api/auth/refresh')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('refresh_token');
});

test('API logout revokes the supplied refresh token and invalidates the access token', function () {
    $user = User::factory()->create();
    $login = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();
    $accessToken = $login->json('access_token');
    $refreshToken = $login->json('refresh_token');

    $this->withToken($accessToken)->postJson('/api/auth/logout', [
        'refresh_token' => $refreshToken,
    ])->assertOk()->assertJsonPath('message', 'Logged out successfully.');

    expect(RefreshToken::sole()->revoked_at)->not->toBeNull();
    $this->withToken($accessToken)->getJson('/api/auth/me')->assertUnauthorized();
    $this->postJson('/api/auth/refresh', ['refresh_token' => $refreshToken])->assertUnauthorized();
});

test('inactive users cannot obtain API tokens', function () {
    $user = User::factory()->create(['is_active' => false]);

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertForbidden()
        ->assertJsonPath('message', 'Your account is inactive.');

    $this->assertDatabaseCount('refresh_tokens', 0);
});

test('an inactive user cannot use an existing refresh token', function () {
    $user = User::factory()->create();
    $refreshToken = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()->json('refresh_token');
    $user->is_active = false;
    $user->save();

    $this->postJson('/api/auth/refresh', ['refresh_token' => $refreshToken])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
    expect(RefreshToken::sole()->revoked_at)->not->toBeNull();
});

test('unverified users cannot obtain API tokens', function () {
    $user = User::factory()->unverified()->create();

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertForbidden()
        ->assertJsonPath('message', 'Email address is not verified.');

});
