<?php

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\CandidateProfile;
use App\Models\JobPost;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

function createApplicationCandidate(): array
{
    $user = User::factory()->create(['role' => 'candidate']);
    $profile = CandidateProfile::factory()->create(['user_id' => $user->id]);
    $job = JobPost::factory()->create();

    return [$user, $profile, $job];
}

test('application store messages use the requested language with English fallback', function (string $language, string $message) {
    [$user, $profile, $job] = createApplicationCandidate();

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/applications', ['job_id' => $job->id])
        ->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('message', $message)
        ->assertJsonPath('data.attributes.status', ApplicationStatus::APPLIED->value);
})->with([
    'Arabic' => ['ar', 'تم تقديم الطلب بنجاح.'],
    'regional Arabic' => ['ar-EG', 'تم تقديم الطلب بنجاح.'],
    'English' => ['en', 'The request has been submitted successfully.'],
    'regional English' => ['en-US', 'The request has been submitted successfully.'],
    'unsupported language' => ['fr-FR', 'The request has been submitted successfully.'],
]);

test('application store validation uses localized attribute names with English fallback', function (string $language, string $message) {
    [$user, $profile, $job] = createApplicationCandidate();

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/applications', [])
        ->assertUnprocessable()
        ->assertJsonPath('errors.job_id.0', $message);
})->with([
    'Arabic' => ['ar-EG', 'حقل الوظيفة مطلوب.'],
    'English' => ['en', 'The job field is required.'],
    'unsupported language' => ['es', 'The job field is required.'],
]);

test('application store rejects duplicate applications with a localized message', function (string $language, string $message) {
    [$user, $profile, $job] = createApplicationCandidate();
    $token = JWTAuth::fromUser($user);

    $this->withToken($token)->postJson('/api/applications', ['job_id' => $job->id])->assertCreated();

    $this->withToken($token)
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/applications', ['job_id' => $job->id])
        ->assertUnprocessable()
        ->assertJsonPath('errors.job_id.0', $message);
})->with([
    'Arabic' => ['ar', 'لقد تقدمت لهذه الوظيفة مسبقاً؛ لا يمكنك التقديم مرتين.'],
    'English' => ['en', 'You have already applied for this job; you cannot apply twice.'],
]);

test('application store rejects unknown jobs with a localized message', function (string $language, string $message) {
    [$user, $profile, $job] = createApplicationCandidate();

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/applications', ['job_id' => 999999])
        ->assertUnprocessable()
        ->assertJsonPath('errors.job_id.0', $message);
})->with([
    'Arabic' => ['ar-EG', 'الوظيفة المطلوبة غير موجودة.'],
    'English' => ['en', 'The requested job does not exist.'],
]);

test('application status updates are localized without changing the stored enum value', function (string $language, string $message) {
    [$user, $profile, $job] = createApplicationCandidate();
    $application = Application::factory()->create([
        'candidate_profile_id' => $profile->id,
        'job_id' => $job->id,
        'status' => ApplicationStatus::APPLIED,
    ]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->patchJson('/api/applications/'.$application->id.'/status', ['status' => ApplicationStatus::IN_REVIEW->value])
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('message', $message)
        ->assertJsonPath('data.attributes.status', ApplicationStatus::IN_REVIEW->value);

    expect($application->fresh()->status)->toBe(ApplicationStatus::IN_REVIEW);
})->with([
    'Arabic' => ['ar', 'تم تحديث حالة الطلب بنجاح.'],
    'English' => ['en-US', 'The application status has been successfully updated.'],
]);

test('application ownership errors are localized without exposing another candidates application', function (string $language, string $message) {
    [$user, $profile, $job] = createApplicationCandidate();
    $application = Application::factory()->create([
        'candidate_profile_id' => $profile->id,
        'job_id' => $job->id,
    ]);
    [$otherUser] = createApplicationCandidate();

    $this->withToken(JWTAuth::fromUser($otherUser))
        ->withHeader('Accept-Language', $language)
        ->patchJson('/api/applications/'.$application->id.'/status', ['status' => ApplicationStatus::IN_REVIEW->value])
        ->assertForbidden()
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('message', $message);
})->with([
    'Arabic' => ['ar', 'غير مصرح لك بالقيام بهذا الإجراء.'],
    'English' => ['en-US', 'Unauthorized'],
    'unsupported language' => ['it', 'Unauthorized'],
]);

test('application withdrawal authorization errors are localized', function (string $language, string $message) {
    [$user, $profile, $job] = createApplicationCandidate();
    $application = Application::factory()->create([
        'candidate_profile_id' => $profile->id,
        'job_id' => $job->id,
    ]);
    [$otherUser] = createApplicationCandidate();

    $this->withToken(JWTAuth::fromUser($otherUser))
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/applications/'.$application->id.'/withdraw')
        ->assertForbidden()
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('message', $message);
})->with([
    'Arabic' => ['ar-EG', 'أنت غير مصرح لك بتنفيذ هذه العمليات.'],
    'English' => ['en', 'You are not authorized to perform these operations.'],
]);

test('missing applications return localized not-found messages', function (string $method, string $endpoint, string $language, string $message) {
    [$user, $profile, $job] = createApplicationCandidate();

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->{$method}($endpoint)
        ->assertNotFound()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic show' => ['getJson', '/api/applications/999999', 'ar-EG', 'لم يتم العثور على الطلب.'],
    'English show' => ['getJson', '/api/applications/999999', 'en', 'Application not found.'],
    'fallback status update' => ['patchJson', '/api/applications/999999/status', 'fr', 'Application not found.'],
]);

test('application invalid status transitions are localized without translating enum values', function (string $language, string $message) {
    [$user, $profile, $job] = createApplicationCandidate();
    $application = Application::factory()->create([
        'candidate_profile_id' => $profile->id,
        'job_id' => $job->id,
        'status' => ApplicationStatus::APPLIED,
    ]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->patchJson('/api/applications/'.$application->id.'/status', ['status' => ApplicationStatus::INTERVIEW->value])
        ->assertUnprocessable()
        ->assertJsonPath('errors.status.0', $message);

    expect($application->fresh()->status)->toBe(ApplicationStatus::APPLIED);
})->with([
    'Arabic' => ['ar', 'لا يمكن الانتقال من الحالة (applied) إلى الحالة (interview).'],
    'English' => ['en-US', 'It is not possible to transition from the state (applied) To state (interview).'],
]);

test('application history notes are localized while status values stay machine-readable', function () {
    [$user, $profile, $job] = createApplicationCandidate();
    $token = JWTAuth::fromUser($user);

    $this->withToken($token)
        ->withHeader('Accept-Language', 'ar')
        ->postJson('/api/applications', ['job_id' => $job->id, 'cover_letter' => 'My custom cover letter 123'])
        ->assertCreated()
        ->assertJsonPath('data.attributes.status', ApplicationStatus::APPLIED->value)
        ->assertJsonPath('data.attributes.cover_letter', 'My custom cover letter 123');

    $application = Application::query()->sole();
    expect($application->histories()->oldest('id')->first()->notes)->toBe('تم إنشاء طلب جديد بحالة متقدم.')
        ->and($application->status)->toBe(ApplicationStatus::APPLIED);

    $this->withToken($token)
        ->withHeader('Accept-Language', 'ar')
        ->patchJson('/api/applications/'.$application->id.'/status', ['status' => ApplicationStatus::IN_REVIEW->value])
        ->assertOk();

    expect($application->histories()->latest('id')->first()->notes)->toBe('تم تحديث حالة الطلب بنجاح.');
});

test('application withdrawal localizes its response and history note without changing stored values', function () {
    [$user, $profile, $job] = createApplicationCandidate();
    $application = Application::factory()->create([
        'candidate_profile_id' => $profile->id,
        'job_id' => $job->id,
        'status' => ApplicationStatus::APPLIED,
    ]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', 'ar-EG')
        ->postJson('/api/applications/'.$application->id.'/withdraw')
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('message', 'تم سحب الطلب بنجاح.')
        ->assertJsonPath('data.attributes.status', ApplicationStatus::WITHDRAWN->value)
        ->assertJsonPath('data.id', (string) $application->id);

    $application->refresh();
    expect($application->status)->toBe(ApplicationStatus::WITHDRAWN)
        ->and($application->histories()->latest('id')->first()->notes)->toBe('قام المرشح بسحب الطلب.');
});

test('application listing and details keep enum values, IDs, and JSON keys unchanged across languages', function (string $language) {
    [$user, $profile, $job] = createApplicationCandidate();
    $application = Application::factory()->create([
        'candidate_profile_id' => $profile->id,
        'job_id' => $job->id,
        'status' => ApplicationStatus::APPLIED,
    ]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->getJson('/api/applications')
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'attributes']]])
        ->assertJsonPath('data.0.attributes.status', ApplicationStatus::APPLIED->value)
        ->assertJsonPath('data.0.id', (string) $application->id);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->getJson('/api/applications/'.$application->id)
        ->assertOk()
        ->assertJsonPath('data.attributes.status', ApplicationStatus::APPLIED->value)
        ->assertJsonPath('data.id', (string) $application->id);
})->with(['Arabic' => ['ar'], 'English' => ['en']]);
