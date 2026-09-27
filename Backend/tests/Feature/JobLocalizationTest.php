<?php

use App\Models\Company;
use App\Models\JobPost;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

test('job authorization errors use the requested language with English fallback', function (string $language, string $message) {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => false]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->getJson('/api/jobs')
        ->assertForbidden()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic' => ['ar', 'حسابك غير نشط.'],
    'regional Arabic' => ['ar-EG', 'حسابك غير نشط.'],
    'English' => ['en', 'Your account is inactive.'],
    'regional English' => ['en-US', 'Your account is inactive.'],
    'unsupported language' => ['fr-FR', 'Your account is inactive.'],
]);

test('unsupported job-feed roles return a localized authorization error', function (string $language, string $message) {
    $user = User::factory()->create(['role' => 'recruiter', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->getJson('/api/jobs')
        ->assertForbidden()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic' => ['ar', 'يمكن للمستخدمين النشطين فقط الوصول إلى الوظائف.'],
    'English' => ['en', 'Only active users can access jobs.'],
]);

test('saved jobs retain localized candidate authorization', function () {
    $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', 'ar')
        ->getJson('/api/saved-jobs')
        ->assertForbidden()
        ->assertExactJson(['message' => 'يمكن للمرشحين فقط الوصول إلى هذا الملف الشخصي.']);
});

test('job-feed validation uses localized attribute names with English fallback', function (string $language, string $message) {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->getJson('/api/jobs?per_page=101')
        ->assertUnprocessable()
        ->assertJsonPath('errors.per_page.0', $message);
})->with([
    'Arabic' => ['ar', 'يجب ألا تكون قيمة حقل عدد العناصر في الصفحة أكبر من 100.'],
    'English' => ['en', 'The items per page field must not be greater than 100.'],
    'unsupported language' => ['de-DE', 'The items per page field must not be greater than 100.'],
]);

test('saved-job validation uses Arabic attribute names', function () {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', 'ar-EG')
        ->getJson('/api/saved-jobs?page=0')
        ->assertUnprocessable()
        ->assertJsonPath('errors.page.0', 'يجب ألا تقل قيمة حقل الصفحة عن 1.');
});

test('missing jobs return localized messages when saving or unsaving', function (string $method, string $language, string $message) {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->{$method}('/api/jobs/999999/save')
        ->assertNotFound()
        ->assertExactJson(['message' => $message]);
})->with([
    'save in Arabic' => ['postJson', 'ar', 'لم يتم العثور على الوظيفة.'],
    'unsave in English' => ['deleteJson', 'en-US', 'Job not found.'],
    'unsave with fallback' => ['deleteJson', 'es', 'Job not found.'],
]);

test('localized job responses preserve user-generated content and machine values', function () {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);
    $company = Company::factory()->create(['name' => 'شركة التقنية المتقدمة']);
    $job = JobPost::factory()->for($company)->create([
        'title' => 'Senior Backend Engineer',
        'job_type' => 'job',
        'work_mode' => 'remote',
    ]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', 'ar')
        ->getJson('/api/jobs')
        ->assertOk()
        ->assertJsonPath('data.0.id', $job->id)
        ->assertJsonPath('data.0.title', 'Senior Backend Engineer')
        ->assertJsonPath('data.0.company.name', 'شركة التقنية المتقدمة')
        ->assertJsonPath('data.0.job_type', 'job')
        ->assertJsonPath('data.0.work_mode', 'remote');
});
