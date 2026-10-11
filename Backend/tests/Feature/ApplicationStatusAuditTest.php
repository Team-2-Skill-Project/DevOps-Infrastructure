<?php

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\CandidateProfile;
use App\Models\JobPost;
use App\Models\Notification;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

function candidateWithProfile(): User
{
    $profile = CandidateProfile::factory()->create();

    return $profile->user;
}

it('audits application creation with candidate source and actor', function () {
    $candidate = candidateWithProfile();
    $job = JobPost::factory()->create();

    $id = $this->withToken(JWTAuth::fromUser($candidate))->postJson('/api/applications', [
        'job_id' => $job->id,
        'cover_letter' => 'I am excited.',
    ])->assertCreated()->json('data.id');

    $log = AuditLog::query()->where('action', 'application_created')->sole();
    expect((int) $log->entity_id)->toBe((int) $id)
        ->and($log->entity_type->value)->toBe('application')
        ->and($log->source->value)->toBe('candidate')
        ->and($log->actor->is($candidate))->toBeTrue()
        ->and($log->before)->toBeNull()
        ->and($log->after)->toBe(['status' => 'applied'])
        ->and($log->metadata['job_id'])->toBe($job->id);
});

it('audits status transitions with old status, new status, actor and source', function () {
    $candidate = candidateWithProfile();
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $application = Application::factory()->create(['candidate_profile_id' => $candidate->candidateProfile->id]);

    $this->withToken(JWTAuth::fromUser($admin))->patchJson("/api/applications/{$application->id}/status", [
        'status' => 'in_review',
        'notes' => 'Looks promising.',
    ])->assertOk();

    $log = AuditLog::query()->where('action', 'application_status_changed')->sole();
    expect((int) $log->entity_id)->toBe($application->id)
        ->and($log->before)->toBe(['status' => 'applied'])
        ->and($log->after)->toBe(['status' => 'in_review'])
        ->and($log->source->value)->toBe('admin')
        ->and($log->actor->is($admin))->toBeTrue()
        ->and($log->metadata['notes'])->toBe('Looks promising.');
});

it('audits candidate withdrawal with candidate source', function () {
    $candidate = candidateWithProfile();
    $application = Application::factory()->create(['candidate_profile_id' => $candidate->candidateProfile->id]);

    $this->withToken(JWTAuth::fromUser($candidate))->postJson("/api/applications/{$application->id}/withdraw")->assertOk();

    $log = AuditLog::query()->where('action', 'application_status_changed')->sole();
    expect($log->before)->toBe(['status' => 'applied'])
        ->and($log->after)->toBe(['status' => 'withdrawn'])
        ->and($log->source->value)->toBe('candidate')
        ->and($log->actor->is($candidate))->toBeTrue();
});

it('does not audit rejected or same-status transitions', function () {
    $candidate = candidateWithProfile();
    $application = Application::factory()->create(['candidate_profile_id' => $candidate->candidateProfile->id]);

    $this->withToken(JWTAuth::fromUser($candidate))->patchJson("/api/applications/{$application->id}/status", [
        'status' => 'applied',
    ])->assertUnprocessable();

    expect(AuditLog::query()->where('action', 'application_status_changed')->count())->toBe(0);
    expect($application->fresh()->status->value)->toBe('applied');
});

it('preserves status history and fires no new notification behavior', function () {
    $candidate = candidateWithProfile();
    $application = Application::factory()->create(['candidate_profile_id' => $candidate->candidateProfile->id]);

    $this->withToken(JWTAuth::fromUser($candidate))->postJson("/api/applications/{$application->id}/withdraw")->assertOk();

    expect($application->histories()->count())->toBe(1)
        ->and($application->histories()->first()->old_status->value)->toBe('applied')
        ->and($application->histories()->first()->new_status->value)->toBe('withdrawn')
        ->and(Notification::query()->count())->toBe(0);
});
