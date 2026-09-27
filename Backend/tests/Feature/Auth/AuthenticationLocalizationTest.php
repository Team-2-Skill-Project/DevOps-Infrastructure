<?php

use App\Models\User;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

test('API authentication messages use the requested supported language', function (string $language, string $message) {
    User::factory()->create(['email' => 'user@example.com']);

    $this->withHeader('Accept-Language', $language)
        ->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'incorrect-password',
        ])
        ->assertUnauthorized()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic' => ['ar', 'بيانات الاعتماد غير صحيحة.'],
    'regional Arabic' => ['ar-EG', 'بيانات الاعتماد غير صحيحة.'],
    'English' => ['en', 'Invalid credentials.'],
    'regional English' => ['en-US', 'Invalid credentials.'],
]);

test('unsupported API languages fall back to English', function () {
    $this->withHeader('Accept-Language', 'fr-FR')
        ->postJson('/api/auth/login', [
            'email' => 'missing@example.com',
            'password' => 'password',
        ])
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Invalid credentials.']);
});

test('API validation errors are localized in Arabic', function () {
    $this->withHeader('Accept-Language', 'ar-EG')
        ->postJson('/api/auth/login')
        ->assertUnprocessable()
        ->assertJsonPath('errors.email.0', 'حقل البريد الإلكتروني مطلوب.')
        ->assertJsonPath('errors.password.0', 'حقل كلمة المرور مطلوب.')
        ->assertJsonPath('message', 'حقل البريد الإلكتروني مطلوب. (وخطأ إضافي واحد)');
});

test('API validation errors remain in English when English is requested', function () {
    $this->withHeader('Accept-Language', 'en-US')
        ->postJson('/api/auth/login')
        ->assertUnprocessable()
        ->assertJsonPath('errors.email.0', 'The email field is required.')
        ->assertJsonPath('errors.password.0', 'The password field is required.');
});

test('API authentication exceptions are localized in Arabic', function () {
    $this->withHeader('Accept-Language', 'ar')
        ->getJson('/api/auth/me')
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'غير مصادق عليه.']);
});

test('API throttling errors are localized in Arabic without changing rate limit headers', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $attempt) {
        $this->withHeader('Accept-Language', 'ar')
            ->postJson('/api/auth/login', [
                'email' => $user->email,
                'password' => 'incorrect-password',
            ])
            ->assertUnauthorized();
    }

    $this->withHeader('Accept-Language', 'ar')
        ->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'incorrect-password',
        ])
        ->assertTooManyRequests()
        ->assertHeader('Retry-After')
        ->assertExactJson(['message' => 'محاولات كثيرة جدًا.']);
});

test('Google authentication errors use the requested language', function () {
    config()->set('services.google', [
        'client_id' => 'test-client',
        'client_secret' => 'test-secret',
        'redirect' => 'http://localhost/auth/google/callback',
    ]);

    $this->withHeader('Accept-Language', 'ar-EG')
        ->get(route('google.callback', ['state' => 'invalid', 'code' => 'test-code']))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors([
            'google' => 'انتهت صلاحية جلسة تسجيل الدخول عبر Google أو كانت غير صحيحة. يرجى المحاولة مرة أخرى.',
        ]);
});

test('localized requests preserve successful JWT authentication behavior', function () {
    $user = User::factory()->create();

    $this->withHeader('Accept-Language', 'ar')
        ->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
        ->assertOk()
        ->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'user'])
        ->assertJsonPath('token_type', 'bearer')
        ->assertJsonPath('user.id', $user->id);
});
