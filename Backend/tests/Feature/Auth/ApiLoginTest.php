<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

test('verified users receive a JWT with the configured lifetime without a web session', function (int $ttl) {
    $this->freezeSecond();
    config()->set('jwt.ttl', $ttl);
    $user = User::factory()->withTwoFactor()->create(['phone' => null]);
    Mail::fake();

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'user'])
        ->assertJsonPath('token_type', 'bearer')
        ->assertJsonPath('expires_in', $ttl * 60)
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonPath('user.email', $user->email)
        ->assertJsonMissingPath('user.password')
        ->assertJsonMissingPath('user.remember_token')
        ->assertJsonMissingPath('user.two_factor_secret')
        ->assertJsonMissingPath('user.two_factor_recovery_codes');
    $this->assertGuest('web');
    Mail::assertNothingSent();

    Auth::forgetGuards();
    $guard = Auth::guard('api')->setToken($response->json('access_token'));

    expect($guard->user()->id)->toBe($user->id);
    expect($guard->payload()->get('exp'))->toBe(now()->addMinutes($ttl)->timestamp);
    $this->assertGuest('web');
})->with(['one hour' => 60, 'fifteen minutes' => 15]);

test('API login rejects invalid credentials with 401', function (string $email, string $password) {
    User::factory()->create(['email' => 'user@example.com']);

    $this->postJson('/api/auth/login', ['email' => $email, 'password' => $password])
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Invalid credentials.']);

    $this->assertGuest('web');
    $this->assertGuest('api');
})->with([
    'unknown email' => ['missing@example.com', 'password'],
    'incorrect password' => ['user@example.com', 'incorrect-password'],
]);

test('API login rejects correct credentials for an unverified email with 403', function () {
    $user = User::factory()->unverified()->create();
    Mail::fake();

    $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password'])
        ->assertForbidden()
        ->assertExactJson(['message' => 'Email address is not verified.']);

    $this->assertGuest('web');
    $this->assertGuest('api');
    Mail::assertNothingSent();
});

test('API login checks the password before reporting an unverified email', function () {
    $user = User::factory()->unverified()->create();

    $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'incorrect-password'])
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Invalid credentials.']);

    $this->assertGuest('api');
});

test('Google-only users cannot use password API login', function () {
    $user = User::factory()->create(['google_id' => 'google-123', 'password' => null]);

    $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password'])
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Invalid credentials.']);

    $this->assertGuest('api');
});

test('API login validates required credentials with 422', function (array $credentials, array $errors) {
    $this->postJson('/api/auth/login', $credentials)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($errors)
        ->assertJsonMissingPath('access_token');

    $this->assertGuest('api');
})->with([
    'missing credentials' => [[], [
        'email' => 'The email field is required.',
        'password' => 'The password field is required.',
    ]],
    'invalid email' => [['email' => 'invalid', 'password' => 'password'], [
        'email' => 'The email field must be a valid email address.',
    ]],
    'invalid password type' => [['email' => 'user@example.com', 'password' => ['password']], [
        'password' => 'The password field must be a string.',
    ]],
]);

test('API login attempts are rate limited without blocking Fortify login', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'incorrect-password'])
            ->assertUnauthorized();
    }

    $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password'])
        ->assertTooManyRequests()
        ->assertJsonMissingPath('access_token');

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user, 'web');
});
