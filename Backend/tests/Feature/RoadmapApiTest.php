<?php

use App\Enums\RoadmapTaskStatus;
use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\JobPost;
use App\Models\Roadmap;
use App\Models\Skill;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\RoadmapGenerationService;
use Tymon\JWTAuth\Facades\JWTAuth;

it('returns 401 for unauthenticated roadmap operations', function (string $method, string $uri) {
    $this->json($method, $uri)->assertUnauthorized();
})->with([
    'generate' => ['POST', '/api/roadmaps'],
    'details' => ['GET', '/api/roadmaps/999'],
    'complete task' => ['PATCH', '/api/roadmap-tasks/999/complete'],
    'refresh' => ['POST', '/api/roadmaps/999/refresh'],
]);

it('returns 403 when a noncandidate attempts roadmap generation', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/roadmaps', [
        'target_type' => 'role',
        'target_role' => 'Backend Engineer',
    ])->assertForbidden();
});

it('generates a role roadmap and returns the complete nested structure', function () {
    $profile = CandidateProfile::factory()->create();

    $response = $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/roadmaps', [
        'target_type' => 'role',
        'target_role' => 'Backend Engineer',
    ])->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.target.type', 'role')
        ->assertJsonPath('data.target.role', 'Backend Engineer')
        ->assertJsonPath('data.progress', 0)
        ->assertJsonCount(3, 'data.phases')
        ->assertJsonStructure([
            'data' => [
                'id', 'title', 'rationale', 'status', 'progress', 'target', 'generation_version', 'generated_at',
                'phases' => [['id', 'order', 'title', 'progress', 'milestones' => [['id', 'order', 'title', 'progress', 'tasks' => [['id', 'order', 'title', 'status', 'completed_at', 'evidence', 'notes']]]]]],
            ],
        ]);

    $this->assertDatabaseHas('roadmaps', [
        'id' => $response->json('data.id'),
        'candidate_profile_id' => $profile->id,
        'target_role' => 'Backend Engineer',
    ]);
});

it('generates a roadmap for an accessible target job and rejects an unavailable job', function () {
    $profile = CandidateProfile::factory()->create();
    $job = JobPost::factory()->create(['title' => 'Platform Engineer']);
    $inactive = JobPost::factory()->create(['is_active' => false]);
    $token = JWTAuth::fromUser($profile->user);

    $this->withToken($token)->postJson('/api/roadmaps', [
        'target_type' => 'job',
        'target_job_id' => $job->id,
    ])->assertCreated()
        ->assertJsonPath('data.target.type', 'job')
        ->assertJsonPath('data.target.id', $job->id)
        ->assertJsonPath('data.target.title', 'Platform Engineer');

    $this->withToken($token)->postJson('/api/roadmaps', [
        'target_type' => 'job',
        'target_job_id' => $inactive->id,
    ])->assertUnprocessable()->assertJsonValidationErrors(['target_job_id']);
});

it('returns complete roadmap details to the owner', function () {
    $profile = CandidateProfile::factory()->create();
    $roadmap = app(RoadmapGenerationService::class)->generate($profile, 'role', 'Backend Engineer');

    $this->withToken(JWTAuth::fromUser($profile->user))->getJson("/api/roadmaps/{$roadmap->id}")
        ->assertOk()->assertJsonPath('data.id', $roadmap->id)
        ->assertJsonPath('data.phases.0.milestones.0.tasks.0.status', RoadmapTaskStatus::PENDING->value);
});

it('returns 404 when another candidate requests roadmap details', function () {
    $profile = CandidateProfile::factory()->create();
    $other = CandidateProfile::factory()->create();
    $roadmap = app(RoadmapGenerationService::class)->generate($profile, 'role', 'Backend Engineer');

    $this->withToken(JWTAuth::fromUser($other->user))->getJson("/api/roadmaps/{$roadmap->id}")
        ->assertNotFound()->assertJsonPath('message', 'Roadmap not found.');
});

it('completes a task idempotently and recalculates the complete hierarchy', function () {
    $profile = CandidateProfile::factory()->create();
    $roadmap = app(RoadmapGenerationService::class)->generate($profile, 'role', 'Backend Engineer');
    $task = $roadmap->phases->first()->milestones->first()->tasks->first();
    $token = JWTAuth::fromUser($profile->user);

    $this->withToken($token)->patchJson("/api/roadmap-tasks/{$task->id}/complete", [
        'evidence' => 'https://example.com/evidence',
        'notes' => 'Reviewed by a mentor.',
    ])->assertOk()
        ->assertJsonPath('data.progress', 16.67)
        ->assertJsonPath('data.phases.0.progress', 50)
        ->assertJsonPath('data.phases.0.milestones.0.progress', 50)
        ->assertJsonPath('data.phases.0.milestones.0.tasks.0.status', RoadmapTaskStatus::COMPLETED->value)
        ->assertJsonPath('data.phases.0.milestones.0.tasks.0.evidence', 'https://example.com/evidence');

    $completedAt = $task->fresh()->completed_at;
    $this->withToken($token)->patchJson("/api/roadmap-tasks/{$task->id}/complete")
        ->assertOk()->assertJsonPath('data.progress', 16.67);

    expect($task->fresh()->completed_at->equalTo($completedAt))->toBeTrue();
});

it('rejects client progress manipulation', function () {
    $profile = CandidateProfile::factory()->create();
    $roadmap = app(RoadmapGenerationService::class)->generate($profile, 'role', 'Backend Engineer');
    $task = $roadmap->phases->first()->milestones->first()->tasks->first();

    $this->withToken(JWTAuth::fromUser($profile->user))->patchJson("/api/roadmap-tasks/{$task->id}/complete", [
        'progress' => 100,
        'status' => 'completed',
    ])->assertUnprocessable()->assertJsonValidationErrors(['progress', 'status']);
    expect($task->fresh()->isCompleted())->toBeFalse();
});

it('returns 404 when another candidate completes a task', function () {
    $profile = CandidateProfile::factory()->create();
    $other = CandidateProfile::factory()->create();
    $roadmap = app(RoadmapGenerationService::class)->generate($profile, 'role', 'Backend Engineer');
    $task = $roadmap->phases->first()->milestones->first()->tasks->first();

    $this->withToken(JWTAuth::fromUser($other->user))->patchJson("/api/roadmap-tasks/{$task->id}/complete")
        ->assertNotFound()->assertJsonPath('message', 'Roadmap task not found.');
});

it('does not refresh when no material trigger changed', function () {
    $profile = CandidateProfile::factory()->create();
    $roadmap = app(RoadmapGenerationService::class)->generate($profile, 'role', 'Backend Engineer');

    $this->withToken(JWTAuth::fromUser($profile->user))->postJson("/api/roadmaps/{$roadmap->id}/refresh")
        ->assertOk()
        ->assertJsonPath('data.id', $roadmap->id)
        ->assertJsonPath('meta.refreshed', false)
        ->assertJsonPath('meta.reasons', []);

    $this->assertDatabaseCount('roadmaps', 1);
});

it('creates an auditable revision for a target change and carries completed work forward', function () {
    $profile = CandidateProfile::factory()->create();
    $roadmap = app(RoadmapGenerationService::class)->generate($profile, 'role', 'Backend Engineer');
    $task = $roadmap->phases->first()->milestones->first()->tasks->first();
    $token = JWTAuth::fromUser($profile->user);
    $this->withToken($token)->patchJson("/api/roadmap-tasks/{$task->id}/complete", ['evidence' => 'portfolio'])->assertOk();

    $response = $this->withToken($token)->postJson("/api/roadmaps/{$roadmap->id}/refresh", [
        'target_type' => 'role',
        'target_role' => 'Platform Engineer',
    ])->assertOk()
        ->assertJsonPath('meta.refreshed', true)
        ->assertJsonPath('meta.reasons.0', 'target_changed')
        ->assertJsonPath('data.previous_roadmap_id', $roadmap->id)
        ->assertJsonPath('data.phases.0.milestones.0.tasks.0.status', RoadmapTaskStatus::COMPLETED->value)
        ->assertJsonPath('data.phases.0.milestones.0.tasks.0.evidence', 'portfolio');

    expect($response->json('data.id'))->not->toBe($roadmap->id)
        ->and($roadmap->fresh()->status)->toBe(Roadmap::STATUS_ARCHIVED)
        ->and($roadmap->fresh()->tasks()->where('status', RoadmapTaskStatus::COMPLETED->value)->count())->toBe(1);
    $this->assertDatabaseCount('roadmaps', 2);
});

it('refreshes after verified skills materially change or progress crosses the threshold', function (string $trigger) {
    $profile = CandidateProfile::factory()->create();
    $roadmap = app(RoadmapGenerationService::class)->generate($profile, 'role', 'Backend Engineer');
    $token = JWTAuth::fromUser($profile->user);

    if ($trigger === 'skills') {
        CandidateSkill::factory()->for($profile)->for(Skill::factory())->create();
    } else {
        foreach ($roadmap->phases->first()->milestones->first()->tasks as $task) {
            $this->withToken($token)->patchJson("/api/roadmap-tasks/{$task->id}/complete")->assertOk();
        }
    }

    $this->withToken($token)->postJson("/api/roadmaps/{$roadmap->id}/refresh")
        ->assertOk()
        ->assertJsonPath('meta.refreshed', true)
        ->assertJsonPath('meta.reasons.0', $trigger === 'skills' ? 'verified_skills_changed' : 'progress_threshold_reached');
})->with(['skills', 'progress']);

it('returns 404 when another candidate attempts to refresh a roadmap', function () {
    $profile = CandidateProfile::factory()->create();
    $other = CandidateProfile::factory()->create();
    $roadmap = app(RoadmapGenerationService::class)->generate($profile, 'role', 'Backend Engineer');

    $this->withToken(JWTAuth::fromUser($other->user))->postJson("/api/roadmaps/{$roadmap->id}/refresh")
        ->assertNotFound()->assertJsonPath('message', 'Roadmap not found.');
});

it('creates roadmap reminders with backward-compatible task metadata', function () {
    $profile = CandidateProfile::factory()->create();
    $roadmap = app(RoadmapGenerationService::class)->generate($profile, 'role', 'Backend Engineer');
    $task = $roadmap->phases->first()->milestones->first()->tasks->first();

    $notification = app(NotificationService::class)
        ->notifyRoadmapReminder($profile->user, $roadmap, $task);

    expect($notification->data)->toMatchArray([
        'roadmap_id' => $roadmap->id,
        'roadmap_step_id' => $task->id,
        'roadmap_task_id' => $task->id,
        'deep_link' => ['type' => 'roadmap', 'id' => $roadmap->id],
    ]);
});
