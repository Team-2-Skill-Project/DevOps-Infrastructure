<?php

/**
 * Task16 final integration — Roadmap Creation.
 *
 * Covers the REAL Task13 journey through API boundaries with a deterministic
 * fake RoadmapGeneratorContract (the only external boundary):
 * target job → generation input built from stored candidate/job context →
 * validated output → storage (phases/milestones/tasks) → API resource
 * (Flutter contract shape) → task completion → progress recalculation →
 * refresh revision with completed-work preservation → ownership isolation.
 *
 * Truthfulness note: "skill gaps" below come from
 * RoadmapGenerationInputBuilder — the stored-JobMatch branch when a JobMatch
 * row pre-exists, otherwise its own fallback diff of required job skills vs
 * candidate skills. There is NO production matching engine feeding it
 * (Job Match is classified C), so the tests never claim otherwise.
 *
 * Classification: A (fully implemented, integration test only).
 */

use App\Contracts\RoadmapGeneratorContract;
use App\Enums\RoadmapStatus;
use App\Enums\RoadmapTaskStatus;
use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\JobMatch;
use App\Models\JobPost;
use App\Models\JobSkill;
use App\Models\Roadmap;
use App\Models\Skill;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

if (! function_exists('roadmapAs')) {
    function roadmapAs(object $test, User $user): object
    {
        auth()->forgetGuards();
        app(JWT::class)->unsetToken();

        return $test->withToken(JWTAuth::fromUser($user));
    }
}

if (! function_exists('bindRoadmapIntegrationGenerator')) {
    function bindRoadmapIntegrationGenerator(string $taskTitle = 'Complete focused Docker practice'): void
    {
        app()->instance(RoadmapGeneratorContract::class, new class($taskTitle) implements RoadmapGeneratorContract
        {
            public function __construct(private string $taskTitle) {}

            public function generate(array $input): array
            {
                return [
                    'rationale' => 'Close the Docker gap with evidence.',
                    'phases' => [[
                        'order' => 1,
                        'title' => 'Foundations',
                        'description' => 'Build the required foundation.',
                        'rationale' => 'The foundation supports later work.',
                        'milestones' => [[
                            'order' => 1,
                            'title' => 'Complete the foundation',
                            'description' => 'Finish a measurable unit of work.',
                            'tasks' => [[
                                'order' => 1,
                                'title' => $this->taskTitle,
                                'description' => 'Finish the exercise and record evidence.',
                                'metadata' => ['week' => 1],
                            ]],
                        ]],
                    ]],
                ];
            }

            public function version(): string
            {
                return 'integration-fake-v1';
            }
        });
    }
}

if (! function_exists('roadmapJobTargetContext')) {
    function roadmapJobTargetContext(): array
    {
        $profile = CandidateProfile::factory()->create(['job_title' => 'Junior Developer']);
        $laravel = Skill::factory()->create(['name' => 'Laravel']);
        $docker = Skill::factory()->create(['name' => 'Docker']);
        CandidateSkill::factory()->for($profile)->for($laravel)->create(['source' => CandidateSkill::SOURCE_MANUAL]);
        $job = JobPost::factory()->create(['title' => 'Backend Engineer', 'canonical_role' => 'Backend Engineer', 'is_active' => true]);
        JobSkill::factory()->for($job)->for($laravel)->create(['is_required' => true, 'importance' => 3]);
        JobSkill::factory()->for($job)->for($docker)->create(['is_required' => true, 'importance' => 5]);

        return [$profile, $job, $docker];
    }
}

it('creates a job-targeted roadmap from real candidate context with fallback skill gaps', function () {
    bindRoadmapIntegrationGenerator();
    [$profile, $job, $docker] = roadmapJobTargetContext();

    $response = roadmapAs($this, $profile->user)->postJson('/api/roadmaps', [
        'target_type' => 'job',
        'target_job_id' => $job->id,
    ])->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.target.type', 'job')
        ->assertJsonPath('data.target.id', $job->id)
        ->assertJsonPath('data.target.title', 'Backend Engineer')
        ->assertJsonPath('data.progress', 0)
        ->assertJsonPath('data.generation_version', 'integration-fake-v1')
        ->assertJsonStructure([
            'data' => [
                'id', 'title', 'rationale', 'status', 'progress', 'target', 'generation_version', 'generated_at',
                'phases' => [['id', 'order', 'title', 'progress', 'milestones' => [['id', 'order', 'title', 'progress', 'tasks' => [['id', 'order', 'title', 'status', 'completed_at', 'evidence', 'notes']]]]]],
            ],
        ]);

    $roadmap = Roadmap::query()->findOrFail($response->json('data.id'));

    // Target persisted + generation input snapshot built from stored context,
    // with the Docker fallback gap (candidate lacks it, job requires it).
    expect($roadmap->candidate_profile_id)->toBe($profile->id)
        ->and($roadmap->target_job_post_id)->toBe($job->id)
        ->and($roadmap->generation_input_snapshot['baseline']['profile']['job_title'])->toBe('Junior Developer')
        ->and($roadmap->generation_input_snapshot['priority_gaps'])->toHaveCount(1)
        ->and($roadmap->generation_input_snapshot['priority_gaps'][0])->toMatchArray([
            'skill_id' => $docker->id, 'name' => 'Docker', 'kind' => 'missing',
        ]);

    $this->assertDatabaseCount('roadmap_phases', 1);
    $this->assertDatabaseCount('roadmap_milestones', 1);
    $this->assertDatabaseCount('roadmap_steps', 1);
});

it('consumes pre-existing stored job-match gaps when the optional match row exists', function () {
    bindRoadmapIntegrationGenerator();
    [$profile, $job, $docker] = roadmapJobTargetContext();
    JobMatch::factory()->for($profile)->for($job)->create([
        'missing_skills' => [['skill_id' => $docker->id, 'importance' => 'critical']],
        'weak_skills' => [],
    ]);

    $response = roadmapAs($this, $profile->user)->postJson('/api/roadmaps', [
        'target_type' => 'job',
        'target_job_id' => $job->id,
    ])->assertCreated();

    $roadmap = Roadmap::query()->findOrFail($response->json('data.id'));

    expect($roadmap->generation_input_snapshot['priority_gaps'][0])->toMatchArray([
        'skill_id' => $docker->id, 'name' => 'Docker', 'kind' => 'missing',
    ]);
});

it('completes tasks recalculates progress refreshes with preservation and isolates owners', function () {
    bindRoadmapIntegrationGenerator('Complete focused Docker practice');
    [$profile, $job] = roadmapJobTargetContext();

    $roadmapId = roadmapAs($this, $profile->user)->postJson('/api/roadmaps', [
        'target_type' => 'job',
        'target_job_id' => $job->id,
    ])->assertCreated()->json('data.id');

    $roadmap = Roadmap::query()->findOrFail($roadmapId);
    $task = $roadmap->tasks()->firstOrFail();

    // Owner detail vs other candidate isolation.
    roadmapAs($this, $profile->user)->getJson("/api/roadmaps/{$roadmapId}")
        ->assertOk()->assertJsonPath('data.id', $roadmapId);

    $other = CandidateProfile::factory()->create();
    roadmapAs($this, $other->user)->getJson("/api/roadmaps/{$roadmapId}")->assertNotFound();
    roadmapAs($this, $other->user)->patchJson("/api/roadmap-tasks/{$task->id}/complete")->assertNotFound();

    // Complete → machine status flips, hierarchy progress recalculates.
    roadmapAs($this, $profile->user)->patchJson("/api/roadmap-tasks/{$task->id}/complete", [
        'evidence' => 'https://example.com/evidence',
        'notes' => 'Docker lab finished.',
    ])->assertOk()
        ->assertJsonPath('data.progress', 100)
        ->assertJsonPath('data.phases.0.milestones.0.tasks.0.status', RoadmapTaskStatus::COMPLETED->value)
        ->assertJsonPath('data.phases.0.milestones.0.tasks.0.evidence', 'https://example.com/evidence');

    // Completing again is idempotent.
    roadmapAs($this, $profile->user)->patchJson("/api/roadmap-tasks/{$task->id}/complete")
        ->assertOk()->assertJsonPath('data.progress', 100);

    // Refresh: progress threshold reached → new revision, previous archived,
    // completed work preserved by title.
    $refreshedId = roadmapAs($this, $profile->user)->postJson("/api/roadmaps/{$roadmapId}/refresh")
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('meta.refreshed', true)
        ->json('data.id');

    expect($refreshedId)->not->toBe($roadmapId)
        ->and(Roadmap::query()->findOrFail($roadmapId)->status)->toBe(RoadmapStatus::ARCHIVED->value);

    $refreshed = Roadmap::query()->findOrFail($refreshedId);
    expect($refreshed->previous_roadmap_id)->toBe($roadmapId)
        ->and((float) $refreshed->overall_progress)->toBe(100.0)
        ->and($refreshed->status)->toBe(RoadmapStatus::COMPLETED->value);

    $carried = $refreshed->tasks()->firstOrFail();
    expect($carried->title)->toBe('Complete focused Docker practice')
        ->and($carried->status)->toBe(RoadmapTaskStatus::COMPLETED);

    // Localization: machine values identical in en and ar.
    foreach (['en', 'ar'] as $locale) {
        roadmapAs($this, $profile->user)->withHeader('Accept-Language', $locale)
            ->getJson("/api/roadmaps/{$refreshedId}")
            ->assertOk()
            ->assertJsonPath('data.status', RoadmapStatus::COMPLETED->value)
            ->assertJsonPath('data.target.type', 'job');
    }
});
