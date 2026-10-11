<?php

/**
 * Task16 final integration — Apply Flow.
 *
 * Covers the REAL candidate journey through API boundaries:
 * job feed → save → saved list → save-again (idempotent) → unsave →
 * saved list empty → save again → apply → duplicate apply rejected →
 * application tracker (list + detail) → status history → audit.
 *
 * Classification: A (fully implemented, integration test only).
 */

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\CandidateProfile;
use App\Models\JobPost;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

if (! function_exists('applyFlowAs')) {
    function applyFlowAs(object $test, User $user): object
    {
        auth()->forgetGuards();
        app(JWT::class)->unsetToken();

        return $test->withToken(JWTAuth::fromUser($user));
    }
}

if (! function_exists('applyFlowCandidate')) {
    function applyFlowCandidate(): array
    {
        $user = User::factory()->create(['role' => 'candidate']);
        $profile = CandidateProfile::factory()->create(['user_id' => $user->id]);
        $job = JobPost::factory()->create(['is_active' => true]);

        return [$user->fresh(), $profile, $job];
    }
}

it('integrates save unsave apply duplicate-prevention and the application tracker', function () {
    [$user, $profile, $job] = applyFlowCandidate();

    // Feed exposes the job with its save state.
    applyFlowAs($this, $user)->getJson('/api/jobs')
        ->assertOk()
        ->assertJsonPath('data.0.id', $job->id)
        ->assertJsonPath('data.0.is_saved', false);

    // Save → 201, then idempotent re-save → 200 without duplicates.
    applyFlowAs($this, $user)->postJson("/api/jobs/{$job->id}/save")
        ->assertCreated()
        ->assertJsonPath('data.job_id', $job->id)
        ->assertJsonPath('data.is_saved', true);
    applyFlowAs($this, $user)->postJson("/api/jobs/{$job->id}/save")
        ->assertOk()
        ->assertJsonPath('data.is_saved', true);

    expect($user->savedJobs()->count())->toBe(1);

    applyFlowAs($this, $user)->getJson('/api/saved-jobs')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $job->id);

    // Feed now reports the job as saved.
    applyFlowAs($this, $user)->getJson('/api/jobs')
        ->assertOk()
        ->assertJsonPath('data.0.is_saved', true);

    // Unsave → saved list empty.
    applyFlowAs($this, $user)->deleteJson("/api/jobs/{$job->id}/save")
        ->assertOk()
        ->assertJsonPath('data.is_saved', false);

    applyFlowAs($this, $user)->getJson('/api/saved-jobs')
        ->assertOk()
        ->assertJsonCount(0, 'data');

    // Save again, then apply → 201 with machine status applied.
    applyFlowAs($this, $user)->postJson("/api/jobs/{$job->id}/save")->assertCreated();

    $applicationId = applyFlowAs($this, $user)->postJson('/api/applications', [
        'job_id' => $job->id,
        'cover_letter' => 'Motivated backend engineer.',
    ])->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.attributes.status', ApplicationStatus::APPLIED->value)
        ->json('data.id');

    // Duplicate apply is rejected with the actual unique-rule semantics.
    applyFlowAs($this, $user)->postJson('/api/applications', ['job_id' => $job->id])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('job_id');

    expect(Application::query()->where('candidate_profile_id', $profile->id)->count())->toBe(1);

    // Tracker list + detail expose the application with its history.
    applyFlowAs($this, $user)->getJson('/api/applications')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.attributes.status', ApplicationStatus::APPLIED->value);

    expect((string) applyFlowAs($this, $user)->getJson('/api/applications')->json('data.0.id'))->toBe((string) $applicationId);

    applyFlowAs($this, $user)->getJson("/api/applications/{$applicationId}")
        ->assertOk()
        ->assertJsonPath('data.attributes.status', ApplicationStatus::APPLIED->value)
        ->assertJsonPath('data.attributes.cover_letter', 'Motivated backend engineer.');

    $application = Application::query()->findOrFail($applicationId);
    expect($application->histories)->toHaveCount(1)
        ->and($application->histories->first()->new_status->value)->toBe(ApplicationStatus::APPLIED->value);

    // Task15 observability: creation audit recorded.
    expect(AuditLog::query()->where('entity_type', 'application')->where('entity_id', $applicationId)->count())->toBeGreaterThan(0);
});

it('isolates applications per candidate and keeps machine values stable across locales', function () {
    [$user, $profile, $job] = applyFlowCandidate();
    [$otherUser, $otherProfile, $otherJob] = applyFlowCandidate();

    $applicationId = applyFlowAs($this, $user)->postJson('/api/applications', ['job_id' => $job->id])
        ->assertCreated()->json('data.id');

    // Another candidate's tracker never contains this application...
    applyFlowAs($this, $otherUser)->getJson('/api/applications')
        ->assertOk()
        ->assertJsonCount(0, 'data');

    // ...and cannot read its detail.
    applyFlowAs($this, $otherUser)->getJson("/api/applications/{$applicationId}")
        ->assertForbidden();

    // Per-candidate uniqueness: another candidate may still apply to the same job.
    applyFlowAs($this, $otherUser)->postJson('/api/applications', ['job_id' => $job->id])
        ->assertCreated()
        ->assertJsonPath('data.attributes.status', ApplicationStatus::APPLIED->value);

    // Localization: machine status identical in en and ar.
    foreach (['en', 'ar'] as $locale) {
        applyFlowAs($this, $otherUser)->withHeader('Accept-Language', $locale)
            ->getJson('/api/applications')
            ->assertOk()
            ->assertJsonPath('data.0.attributes.status', ApplicationStatus::APPLIED->value);
    }

    expect(Application::query()->where('candidate_profile_id', $otherProfile->id)->count())->toBe(1);
});
