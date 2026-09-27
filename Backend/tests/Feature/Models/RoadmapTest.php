<?php

use App\Models\CandidateProfile;
use App\Models\JobPost;
use App\Models\Roadmap;
use App\Models\RoadmapStep;
use App\Models\Skill;
use Illuminate\Database\QueryException;

it('creates a roadmap without a target job using database defaults', function () {
    $profile = CandidateProfile::factory()->create();

    $roadmap = Roadmap::query()->create(['candidate_profile_id' => $profile->id, 'title' => 'Learn backend development'])->fresh();

    expect($roadmap->title)->toBe('Learn backend development');
    expect($roadmap->overall_progress)->toBe('0.00');
    expect($roadmap->status)->toBe(Roadmap::STATUS_ACTIVE);
    expect($roadmap->target_job_post_id)->toBeNull();
    expect($roadmap->targetJobPost)->toBeNull();
    expect($roadmap->description)->toBeNull();
    expect($roadmap->candidateProfile->is($profile))->toBeTrue();
});

it('allows multiple roadmaps per candidate and target job', function () {
    $profile = CandidateProfile::factory()->create();
    $job = JobPost::factory()->create();
    $roadmaps = Roadmap::factory()->count(2)->for($profile)->for($job, 'targetJobPost')->create();
    $anotherCandidate = Roadmap::factory()->for($job, 'targetJobPost')->create();

    expect($profile->roadmaps->modelKeys())->toEqualCanonicalizing($roadmaps->modelKeys());
    expect($job->roadmaps->modelKeys())->toEqualCanonicalizing([...$roadmaps->modelKeys(), $anotherCandidate->id]);
    expect($roadmaps->first()->targetJobPost->is($job))->toBeTrue();
});

it('persists explicit progress and supported roadmap statuses', function (string $progress, string $status) {
    $roadmap = Roadmap::factory()->create();

    $roadmap->update(['overall_progress' => $progress, 'status' => $status, 'description' => 'Backend learning plan']);

    expect($roadmap->fresh()->only(['overall_progress', 'status', 'description']))
        ->toBe(['overall_progress' => $progress, 'status' => $status, 'description' => 'Backend learning plan']);
})->with([
    ['0.00', Roadmap::STATUS_ACTIVE], ['42.75', Roadmap::STATUS_ARCHIVED], ['100.00', Roadmap::STATUS_COMPLETED],
]);

it('creates a step without target skill or resources using database defaults', function () {
    $roadmap = Roadmap::factory()->create();

    $step = RoadmapStep::query()->create(['roadmap_id' => $roadmap->id, 'step_order' => 1, 'title' => 'Build an API'])->fresh();

    expect($step->roadmap->is($roadmap))->toBeTrue();
    expect($step->status)->toBe(RoadmapStep::STATUS_PENDING);
    expect($step->targetSkill)->toBeNull();
    expect($step->target_skill_id)->toBeNull();
    expect($step->resources)->toBeNull();
    expect($step->description)->toBeNull();
});

it('links target skills and returns roadmap steps in their explicit order', function () {
    $roadmap = Roadmap::factory()->create();
    $skill = Skill::factory()->create();
    $second = RoadmapStep::factory()->for($roadmap)->for($skill, 'targetSkill')->create(['step_order' => 2]);
    $first = RoadmapStep::factory()->for($roadmap)->for($skill, 'targetSkill')->create(['step_order' => 1]);

    expect($roadmap->steps->modelKeys())->toBe([$first->id, $second->id]);
    expect($first->targetSkill->is($skill))->toBeTrue();
    expect($skill->roadmapSteps->modelKeys())->toEqualCanonicalizing([$first->id, $second->id]);
});

it('persists structured resources including null URLs without normalization', function () {
    $step = RoadmapStep::factory()->create();
    $resources = [
        ['title' => 'Laravel Documentation', 'url' => 'https://laravel.com/docs', 'type' => 'documentation'],
        ['title' => 'Practice REST API Project', 'url' => null, 'type' => 'project'],
    ];

    $step->update(['resources' => $resources, 'description' => 'Practice backend development']);

    expect($step->fresh()->resources)->toBe($resources);
    expect($step->fresh()->toArray()['resources'])->toBe($resources);
    expect($step->fresh()->description)->toBe('Practice backend development');
});

it('preserves empty resource arrays', function () {
    $step = RoadmapStep::factory()->create(['resources' => []]);

    expect($step->fresh()->resources)->toBe([]);
});

it('persists supported step statuses without automatically calculating progress', function (string $status) {
    $roadmap = Roadmap::factory()->create(['overall_progress' => '25.00']);
    $step = RoadmapStep::factory()->for($roadmap)->create();

    $step->update(['status' => $status]);

    expect($step->fresh()->status)->toBe($status);
    expect($roadmap->fresh()->overall_progress)->toBe('25.00');
})->with([RoadmapStep::STATUS_PENDING, RoadmapStep::STATUS_IN_PROGRESS, RoadmapStep::STATUS_COMPLETED]);

it('rejects duplicate step order within one roadmap', function () {
    $step = RoadmapStep::factory()->create(['step_order' => 1]);

    expect(fn () => RoadmapStep::factory()->create(['roadmap_id' => $step->roadmap_id, 'step_order' => 1]))
        ->toThrow(QueryException::class);
});

it('allows the same step order on different roadmaps', function () {
    $steps = RoadmapStep::factory()->count(2)->create(['step_order' => 1]);

    expect($steps[0]->roadmap_id)->not->toBe($steps[1]->roadmap_id);
    $this->assertDatabaseCount('roadmap_steps', 2);
});

it('deleting a candidate cascades its roadmaps and steps only', function () {
    $profile = CandidateProfile::factory()->create();
    $job = JobPost::factory()->create();
    $skill = Skill::factory()->create();
    $roadmaps = Roadmap::factory()->count(2)->for($profile)->for($job, 'targetJobPost')->create();
    $steps = $roadmaps->map(fn (Roadmap $roadmap): RoadmapStep => RoadmapStep::factory()->for($roadmap)->for($skill, 'targetSkill')->create());
    $other = RoadmapStep::factory()->create();

    $profile->delete();

    foreach ($roadmaps as $roadmap) {
        $this->assertModelMissing($roadmap);
    }
    foreach ($steps as $step) {
        $this->assertModelMissing($step);
    }
    $this->assertModelExists($job);
    $this->assertModelExists($skill);
    $this->assertModelExists($other);
    $this->assertModelExists($other->roadmap);
});

it('deleting a roadmap cascades its steps and preserves parent and targets', function () {
    $job = JobPost::factory()->create();
    $roadmap = Roadmap::factory()->for($job, 'targetJobPost')->create();
    $profile = $roadmap->candidateProfile;
    $skill = Skill::factory()->create();
    $steps = RoadmapStep::factory()->count(2)->for($roadmap)->for($skill, 'targetSkill')
        ->sequence(['step_order' => 1], ['step_order' => 2])->create();
    $other = RoadmapStep::factory()->create();

    $roadmap->delete();

    foreach ($steps as $step) {
        $this->assertModelMissing($step);
    }
    $this->assertModelExists($profile);
    $this->assertModelExists($job);
    $this->assertModelExists($skill);
    $this->assertModelExists($other);
});

it('deleting the target job clears its reference and preserves roadmaps and steps', function () {
    $job = JobPost::factory()->create();
    $roadmap = Roadmap::factory()->for($job, 'targetJobPost')->create();
    $step = RoadmapStep::factory()->for($roadmap)->create();

    $job->delete();

    $this->assertModelExists($roadmap);
    $this->assertModelExists($step);
    expect($roadmap->fresh()->target_job_post_id)->toBeNull();
    expect($roadmap->fresh()->targetJobPost)->toBeNull();
});

it('deleting the target skill clears its reference and preserves steps and roadmap', function () {
    $skill = Skill::factory()->create();
    $step = RoadmapStep::factory()->for($skill, 'targetSkill')->create();
    $roadmap = $step->roadmap;

    $skill->delete();

    $this->assertModelExists($step);
    $this->assertModelExists($roadmap);
    expect($step->fresh()->target_skill_id)->toBeNull();
    expect($step->fresh()->targetSkill)->toBeNull();
});

it('requires roadmap and step titles', function (string $model) {
    expect(fn () => $model::factory()->create(['title' => null]))->toThrow(QueryException::class);
})->with([Roadmap::class, RoadmapStep::class]);
