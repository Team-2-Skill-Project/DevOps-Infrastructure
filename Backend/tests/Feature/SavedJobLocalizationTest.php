<?php

use App\Models\JobPost;
use App\Models\SavedJob;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

function createSavedJobCandidate(): User
{
    return User::factory()->create(['role' => 'candidate']);
}

test('saved job store messages use the requested language with English fallback', function (string $language, string $message) {
    $candidate = createSavedJobCandidate();
    $job = JobPost::factory()->create();

    $this->withToken(JWTAuth::fromUser($candidate))
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/jobs/'.$job->id.'/save')
        ->assertCreated()
        ->assertJsonPath('message', $message)
        ->assertJsonPath('data.job_id', $job->id)
        ->assertJsonPath('data.is_saved', true);
})->with([
    'Arabic' => ['ar', 'تم حفظ الوظيفة بنجاح.'],
    'regional Arabic' => ['ar-EG', 'تم حفظ الوظيفة بنجاح.'],
    'English' => ['en', 'Job saved successfully.'],
    'regional English' => ['en-US', 'Job saved successfully.'],
    'unsupported language' => ['fr-FR', 'Job saved successfully.'],
]);

test('saving an already-saved job is idempotent and keeps a single record', function (string $language, string $message) {
    $candidate = createSavedJobCandidate();
    $job = JobPost::factory()->create();
    $token = JWTAuth::fromUser($candidate);

    $this->withToken($token)->postJson('/api/jobs/'.$job->id.'/save')->assertCreated();
    $this->withToken($token)
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/jobs/'.$job->id.'/save')
        ->assertOk()
        ->assertJsonPath('message', $message)
        ->assertJsonPath('data.job_id', $job->id)
        ->assertJsonPath('data.is_saved', true);

    expect(SavedJob::query()->where('user_id', $candidate->id)->where('job_post_id', $job->id)->count())->toBe(1);
})->with([
    'Arabic' => ['ar', 'تم حفظ الوظيفة بنجاح.'],
    'English' => ['en', 'Job saved successfully.'],
]);

test('unsaving a job localizes its response and stays idempotent', function (string $language, string $message) {
    $candidate = createSavedJobCandidate();
    $job = JobPost::factory()->create();
    SavedJob::factory()->create(['user_id' => $candidate->id, 'job_post_id' => $job->id]);
    $token = JWTAuth::fromUser($candidate);

    $this->withToken($token)
        ->withHeader('Accept-Language', $language)
        ->deleteJson('/api/jobs/'.$job->id.'/save')
        ->assertOk()
        ->assertJsonPath('message', $message)
        ->assertJsonPath('data.job_id', $job->id)
        ->assertJsonPath('data.is_saved', false);

    $this->withToken($token)->deleteJson('/api/jobs/'.$job->id.'/save')->assertOk();

    expect(SavedJob::query()->where('user_id', $candidate->id)->where('job_post_id', $job->id)->exists())->toBeFalse();
})->with([
    'Arabic' => ['ar-EG', 'تمت إزالة الوظيفة من الوظائف المحفوظة.'],
    'English' => ['en-US', 'Job removed from saved jobs.'],
]);

test('saving or unsaving a missing job returns a localized not-found message', function (string $method, string $language, string $message) {
    $candidate = createSavedJobCandidate();

    $this->withToken(JWTAuth::fromUser($candidate))
        ->withHeader('Accept-Language', $language)
        ->{$method}('/api/jobs/999999/save')
        ->assertNotFound()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic save' => ['postJson', 'ar', 'لم يتم العثور على الوظيفة.'],
    'English save' => ['postJson', 'en-US', 'Job not found.'],
    'Arabic unsave' => ['deleteJson', 'ar-EG', 'لم يتم العثور على الوظيفة.'],
    'fallback unsave' => ['deleteJson', 'fr', 'Job not found.'],
]);

test('unknown saved-jobs paths return localized not-found messages', function (string $language, string $message) {
    $candidate = createSavedJobCandidate();

    $this->withToken(JWTAuth::fromUser($candidate))
        ->withHeader('Accept-Language', $language)
        ->getJson('/api/saved-jobs/999999')
        ->assertNotFound()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic' => ['ar', 'لم يتم العثور على الوظيفة المحفوظة.'],
    'regional Arabic' => ['ar-EG', 'لم يتم العثور على الوظيفة المحفوظة.'],
    'English' => ['en', 'Saved job not found.'],
    'fallback' => ['de-DE', 'Saved job not found.'],
]);

test('saved-jobs authentication errors use the requested language with English fallback', function (string $language, string $message) {
    $this->withHeader('Accept-Language', $language)
        ->getJson('/api/saved-jobs')
        ->assertUnauthorized()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic' => ['ar', 'غير مصادق عليه.'],
    'regional Arabic' => ['ar-EG', 'غير مصادق عليه.'],
    'English' => ['en', 'Unauthenticated.'],
    'regional English' => ['en-US', 'Unauthenticated.'],
    'unsupported language' => ['fr-FR', 'Unauthenticated.'],
]);

test('inactive candidates are blocked from saved jobs with a localized message', function (string $language, string $message) {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => false]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->getJson('/api/saved-jobs')
        ->assertForbidden()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic' => ['ar', 'حسابك غير نشط.'],
    'English' => ['en-US', 'Your account is inactive.'],
    'unsupported language' => ['it', 'Your account is inactive.'],
]);

test('non-candidate roles are blocked from saved jobs with a localized message', function (string $language, string $message) {
    $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/jobs/1/save')
        ->assertForbidden()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic' => ['ar-EG', 'يمكن للمرشحين فقط الوصول إلى هذا الملف الشخصي.'],
    'English' => ['en', 'Only candidates can access this profile.'],
]);

test('saved-jobs list validation uses localized attribute names with English fallback', function (string $language, string $message) {
    $candidate = createSavedJobCandidate();

    $this->withToken(JWTAuth::fromUser($candidate))
        ->withHeader('Accept-Language', $language)
        ->getJson('/api/saved-jobs?per_page=101')
        ->assertUnprocessable()
        ->assertJsonPath('errors.per_page.0', $message);
})->with([
    'Arabic' => ['ar', 'يجب ألا تكون قيمة حقل عدد العناصر في الصفحة أكبر من 100.'],
    'English' => ['en-US', 'The items per page field must not be greater than 100.'],
    'unsupported language' => ['es', 'The items per page field must not be greater than 100.'],
]);

test('saved-jobs listing keeps ownership isolated and job content unchanged across languages', function () {
    $candidate = createSavedJobCandidate();
    $other = createSavedJobCandidate();
    $job = JobPost::factory()->create();
    $otherJob = JobPost::factory()->create();
    SavedJob::factory()->create(['user_id' => $candidate->id, 'job_post_id' => $job->id]);
    SavedJob::factory()->create(['user_id' => $other->id, 'job_post_id' => $otherJob->id]);

    $arabic = $this->withToken(JWTAuth::fromUser($candidate))
        ->withHeader('Accept-Language', 'ar')
        ->getJson('/api/saved-jobs')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $job->id)
        ->assertJsonPath('data.0.is_saved', true)
        ->json('data.0');

    $english = $this->withToken(JWTAuth::fromUser($candidate))
        ->withHeader('Accept-Language', 'en-US')
        ->getJson('/api/saved-jobs')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->json('data.0');

    expect($arabic['title'])->toBe($english['title'])
        ->and($arabic['company']['name'])->toBe($english['company']['name'])
        ->and($arabic['employment_type'])->toBe($english['employment_type'])
        ->and($arabic['published_at'])->toBe($english['published_at']);
});

test('saved job responses keep machine-readable keys, IDs, and flags', function () {
    $candidate = createSavedJobCandidate();
    $job = JobPost::factory()->create();

    $this->withToken(JWTAuth::fromUser($candidate))
        ->withHeader('Accept-Language', 'ar')
        ->postJson('/api/jobs/'.$job->id.'/save')
        ->assertCreated()
        ->assertJsonStructure(['message', 'data' => ['job_id', 'is_saved']])
        ->assertJsonPath('data.job_id', $job->id);

    expect(SavedJob::query()->where('user_id', $candidate->id)->where('job_post_id', $job->id)->exists())->toBeTrue();
});
