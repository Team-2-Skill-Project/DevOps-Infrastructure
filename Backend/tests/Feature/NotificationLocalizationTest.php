<?php

use App\Models\Notification;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

test('notification authorization errors use the requested language with English fallback', function (string $language, string $message) {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => false]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->getJson('/api/notifications')
        ->assertForbidden()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic' => ['ar', 'حسابك غير نشط.'],
    'regional Arabic' => ['ar-EG', 'حسابك غير نشط.'],
    'English' => ['en', 'Your account is inactive.'],
    'regional English' => ['en-US', 'Your account is inactive.'],
    'unsupported language' => ['fr-FR', 'Your account is inactive.'],
]);

test('non-candidates receive the existing localized authorization response', function () {
    $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', 'ar')
        ->getJson('/api/notifications/unread-count')
        ->assertForbidden()
        ->assertExactJson(['message' => 'يمكن للمرشحين فقط الوصول إلى هذا الملف الشخصي.']);
});

test('notification validation uses localized attribute names with English fallback', function (string $language, string $message) {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->getJson('/api/notifications?per_page=101')
        ->assertUnprocessable()
        ->assertJsonPath('errors.per_page.0', $message);
})->with([
    'Arabic' => ['ar', 'يجب ألا تكون قيمة حقل عدد العناصر في الصفحة أكبر من 100.'],
    'regional Arabic' => ['ar-EG', 'يجب ألا تكون قيمة حقل عدد العناصر في الصفحة أكبر من 100.'],
    'English' => ['en', 'The items per page field must not be greater than 100.'],
    'regional English' => ['en-US', 'The items per page field must not be greater than 100.'],
    'unsupported language' => ['de-DE', 'The items per page field must not be greater than 100.'],
]);

test('missing notifications return localized ownership-safe responses', function (string $language, string $message) {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->patchJson('/api/notifications/999999/read')
        ->assertNotFound()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic' => ['ar-EG', 'لم يتم العثور على الإشعار.'],
    'English' => ['en-US', 'Notification not found.'],
    'unsupported language' => ['es', 'Notification not found.'],
]);

test('another candidates notification returns the same localized 404 without changing it', function () {
    $candidate = User::factory()->create(['role' => 'candidate', 'is_active' => true]);
    $other = User::factory()->create(['role' => 'candidate', 'is_active' => true]);
    $notification = $other->appNotifications()->create([
        'type' => Notification::TYPE_RELEVANT_JOB,
        'title' => 'New relevant job',
        'message' => 'A new job matching your profile is available.',
    ]);

    $this->withToken(JWTAuth::fromUser($candidate))
        ->withHeader('Accept-Language', 'ar')
        ->patchJson('/api/notifications/'.$notification->id.'/read')
        ->assertNotFound()
        ->assertExactJson(['message' => 'لم يتم العثور على الإشعار.']);

    expect($notification->fresh()->read_at)->toBeNull();
});

test('localized requests preserve stored notification content and machine values', function () {
    $candidate = User::factory()->create(['role' => 'candidate', 'is_active' => true]);
    $notification = $candidate->appNotifications()->create([
        'type' => Notification::TYPE_RELEVANT_JOB,
        'title' => 'Custom stored title',
        'message' => 'رسالة محفوظة كما هي.',
        'data' => ['deep_link' => ['type' => 'job', 'id' => 123]],
    ]);

    $this->withToken(JWTAuth::fromUser($candidate))
        ->withHeader('Accept-Language', 'ar')
        ->getJson('/api/notifications')
        ->assertOk()
        ->assertJsonPath('data.0.id', $notification->id)
        ->assertJsonPath('data.0.type', 'relevant_job')
        ->assertJsonPath('data.0.title', 'Custom stored title')
        ->assertJsonPath('data.0.message', 'رسالة محفوظة كما هي.')
        ->assertJsonPath('data.0.data.deep_link.type', 'job');
});
