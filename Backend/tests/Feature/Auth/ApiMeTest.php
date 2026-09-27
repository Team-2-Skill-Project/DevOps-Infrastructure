<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

test('a login JWT can retrieve the authenticated user without exposing hidden fields', function () {
    $user = User::factory()->withTwoFactor()->create();
    $token = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()->json('access_token');
    Auth::forgetGuards();
    JWTAuth::unsetToken();

    $this->withToken($token)->getJson('/api/auth/me')
        ->assertOk()
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonPath('user.name', $user->name)
        ->assertJsonPath('user.email', $user->email)
        ->assertJsonMissingPath('user.password')
        ->assertJsonMissingPath('user.remember_token')
        ->assertJsonMissingPath('user.two_factor_secret')
        ->assertJsonMissingPath('user.two_factor_recovery_codes');

    $this->assertGuest('web');
});

test('the JWT user endpoint rejects a missing token with JSON 401', function () {
    $this->getJson('/api/auth/me')
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Unauthenticated.']);
});

test('the JWT user endpoint rejects an invalid token with JSON 401', function () {
    $this->withToken('invalid-token')->getJson('/api/auth/me')
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Unauthenticated.']);
});

test('the JWT user endpoint rejects an expired token with JSON 401', function () {
    $this->freezeSecond();
    config()->set('jwt.ttl', 1);
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $this->travel(61)->seconds();

    $this->withToken($token)->getJson('/api/auth/me')
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Unauthenticated.']);
});

test('a web session cannot access the JWT user endpoint without a token', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'web')->getJson('/api/auth/me')
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Unauthenticated.']);
});
