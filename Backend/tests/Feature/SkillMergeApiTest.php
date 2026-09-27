<?php

use App\Models\CandidateSkill;
use App\Models\JobSkill;
use App\Models\RoadmapStep;
use App\Models\Skill;
use App\Models\SkillAlias;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Exceptions;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

test('merge requires JWT', function () {
    $this->postJson('/api/admin/skills/1/merge')->assertUnauthorized();
});

test('merge rejects candidates and inactive administrators', function (string $role, bool $active) {
    $user = User::factory()->create(['role' => $role, 'is_active' => $active]);

    $this->withToken(JWTAuth::fromUser($user))->postJson('/api/admin/skills/1/merge')->assertForbidden();
})->with([['candidate', true], ['admin', false], ['super_admin', false]]);

test('merge validates target and rejects self merge', function (string $case) {
    $admin = User::factory()->create(['role' => 'admin']);
    $source = Skill::factory()->create();
    $data = match ($case) {
        'missing' => [], 'absent' => ['target_skill_id' => 999999], 'self' => ['target_skill_id' => $source->id],
        'invalid' => ['target_skill_id' => []],
    };

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/skills/'.$source->id.'/merge', $data)
        ->assertUnprocessable()->assertJsonValidationErrors(['target_skill_id']);

    $this->assertModelExists($source);
})->with(['missing', 'absent', 'self', 'invalid']);

test('missing source returns 404', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/skills/999999/merge')->assertNotFound();
});

test('administrators merge all source references while preserving target identity and searchability', function (string $role) {
    $admin = User::factory()->create(['role' => $role, 'is_active' => true]);
    $source = Skill::factory()->create(['name' => 'Postgres', 'normalized_name' => 'postgres']);
    $target = Skill::factory()->create(['name' => 'PostgreSQL', 'normalized_name' => 'postgresql', 'category' => 'Database']);
    $targetAttributes = $target->refresh()->getAttributes();
    $candidate = CandidateSkill::factory()->for($source)->create(['proficiency_level' => 'Advanced', 'source' => 'cv_extracted', 'confidence' => '0.80', 'evidence' => ['Project']]);
    $job = JobSkill::factory()->for($source)->create(['importance' => 4, 'required_level' => 'advanced']);
    $step = RoadmapStep::factory()->for($source, 'targetSkill')->create();
    $alias = SkillAlias::factory()->for($source)->create(['alias' => 'PG']);
    $existingTargetAlias = SkillAlias::factory()->for($target)->create(['alias' => 'PostgreSQL database']);
    $this->withToken(JWTAuth::fromUser($admin));

    $this->postJson('/api/admin/skills/'.$source->id.'/merge', ['target_skill_id' => $target->id])
        ->assertOk()->assertJsonPath('data.id', $target->id)->assertJsonPath('meta.merged_skill_id', $source->id)
        ->assertJsonPath('meta.candidate_skill_links_moved', 1)->assertJsonPath('meta.job_skill_links_moved', 1)
        ->assertJsonPath('meta.aliases_moved', 1)->assertJsonPath('meta.roadmap_step_references_moved', 1);

    $this->assertModelMissing($source);
    expect($target->fresh()->getAttributes())->toBe($targetAttributes);
    expect($candidate->fresh()->only(['skill_id', 'proficiency_level', 'source', 'confidence', 'evidence']))
        ->toBe(['skill_id' => $target->id, 'proficiency_level' => 'Advanced', 'source' => 'cv_extracted', 'confidence' => '0.80', 'evidence' => ['Project']]);
    expect($job->fresh()->skill_id)->toBe($target->id);
    expect($step->fresh()->target_skill_id)->toBe($target->id);
    expect($alias->fresh()->skill_id)->toBe($target->id);
    $this->assertModelExists($existingTargetAlias);
    $this->assertDatabaseHas('skill_aliases', ['skill_id' => $target->id, 'normalized_alias' => 'postgres']);
    foreach (['Postgres', 'PG'] as $query) {
        $this->getJson('/api/skills/search?q='.$query)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $target->id);
    }
})->with(['admin', 'super_admin']);

test('candidate duplicates consolidate evidence and confidence without guessing provenance or proficiency', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $source = Skill::factory()->create();
    $target = Skill::factory()->create();
    $targetLink = CandidateSkill::factory()->for($target)->create([
        'source' => 'ai_suggested', 'proficiency_level' => 'Intermediate', 'confidence' => '0.50',
        'evidence' => [['text' => 'Shared evidence'], ['text' => 'Target evidence']],
    ]);
    $sourceLink = CandidateSkill::factory()->for($source)->create([
        'candidate_profile_id' => $targetLink->candidate_profile_id,
        'source' => 'manual', 'proficiency_level' => 'Expert', 'confidence' => '0.90',
        'evidence' => [['text' => 'Shared evidence'], ['text' => 'Source evidence']],
    ]);

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/skills/'.$source->id.'/merge', ['target_skill_id' => $target->id])
        ->assertOk()->assertJsonPath('meta.candidate_skill_links_consolidated', 1);

    expect($targetLink->fresh()->only(['source', 'proficiency_level', 'confidence', 'evidence']))->toBe([
        'source' => 'ai_suggested', 'proficiency_level' => 'Intermediate', 'confidence' => '0.90',
        'evidence' => [['text' => 'Shared evidence'], ['text' => 'Target evidence'], ['text' => 'Source evidence']],
    ]);
    $this->assertModelMissing($sourceLink);
    $this->assertDatabaseCount('candidate_skills', 1);
});

test('candidate consolidation fills nulls and preserves structured evidence documents', function (?string $targetConfidence, ?string $sourceConfidence, ?string $expectedConfidence) {
    $admin = User::factory()->create(['role' => 'admin']);
    $source = Skill::factory()->create();
    $target = Skill::factory()->create();
    $targetLink = CandidateSkill::factory()->for($target)->create(['proficiency_level' => null, 'confidence' => $targetConfidence, 'evidence' => ['text' => 'Target', 'pages' => [1]]]);
    CandidateSkill::factory()->for($source)->create([
        'candidate_profile_id' => $targetLink->candidate_profile_id, 'proficiency_level' => 'Custom level', 'confidence' => $sourceConfidence,
        'evidence' => ['text' => 'Source', 'pages' => [2]],
    ]);

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/skills/'.$source->id.'/merge', ['target_skill_id' => $target->id])->assertOk();

    expect($targetLink->fresh()->proficiency_level)->toBe('Custom level');
    expect($targetLink->fresh()->confidence)->toBe($expectedConfidence);
    expect($targetLink->fresh()->evidence)->toBe([['text' => 'Target', 'pages' => [1]], ['text' => 'Source', 'pages' => [2]]]);
})->with([[null, '0.70', '0.70'], ['0.90', '0.50', '0.90'], ['0.60', null, '0.60'], [null, null, null]]);

test('job duplicate consolidation follows required flag numeric importance and known level policies', function (?int $importance, ?string $level, ?int $expectedImportance, string $expectedLevel) {
    $admin = User::factory()->create(['role' => 'admin']);
    $source = Skill::factory()->create();
    $target = Skill::factory()->create();
    $targetLink = JobSkill::factory()->for($target)->create(['is_required' => false, 'importance' => $importance, 'required_level' => $level]);
    $sourceLink = JobSkill::factory()->for($source)->create(['job_post_id' => $targetLink->job_post_id, 'is_required' => true, 'importance' => 5, 'required_level' => 'advanced']);

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/skills/'.$source->id.'/merge', ['target_skill_id' => $target->id])
        ->assertOk()->assertJsonPath('meta.job_skill_links_consolidated', 1);

    expect($targetLink->fresh()->only(['is_required', 'importance', 'required_level']))
        ->toBe(['is_required' => true, 'importance' => $expectedImportance, 'required_level' => $expectedLevel]);
    $this->assertModelMissing($sourceLink);
    $this->assertDatabaseCount('job_skills', 1);
})->with([[2, 'beginner', 2, 'advanced'], [null, null, 5, 'advanced'], [3, 'expert', 3, 'expert'], [4, 'custom', 4, 'custom']]);

test('redundant aliases collapse and an existing target alias for the old source name is reused', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $source = Skill::factory()->create(['name' => 'Postgres', 'normalized_name' => 'postgres']);
    $target = Skill::factory()->create(['name' => 'PostgreSQL', 'normalized_name' => 'postgresql']);
    $existing = SkillAlias::factory()->for($target)->create(['alias' => 'Postgres']);
    $redundant = SkillAlias::factory()->for($source)->create(['alias' => 'PostgreSQL']);

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/skills/'.$source->id.'/merge', ['target_skill_id' => $target->id])
        ->assertOk()->assertJsonPath('meta.aliases_collapsed', 1)->assertJsonPath('meta.source_name_aliases_created', 0);

    $this->assertModelExists($existing);
    $this->assertModelMissing($redundant);
    $this->assertDatabaseCount('skill_aliases', 1);
});

test('third party name collisions roll back every earlier movement', function (string $collision) {
    $admin = User::factory()->create(['role' => 'admin']);
    $source = Skill::factory()->create(['name' => 'Postgres', 'normalized_name' => 'postgres']);
    $target = Skill::factory()->create();
    $link = CandidateSkill::factory()->for($source)->create();
    $job = JobSkill::factory()->for($source)->create();
    $step = RoadmapStep::factory()->for($source, 'targetSkill')->create();
    $alias = SkillAlias::factory()->for($source)->create(['alias' => 'PG']);
    if ($collision === 'canonical') {
        Skill::factory()->create(['name' => 'PG', 'normalized_name' => 'pg']);
    } else {
        SkillAlias::factory()->create(['alias' => 'Postgres']);
    }
    $models = [$source, $target, $link, $job, $step, $alias];
    $snapshots = array_map(fn (Model $model): array => $model->refresh()->getAttributes(), $models);

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/skills/'.$source->id.'/merge', ['target_skill_id' => $target->id])
        ->assertUnprocessable();

    foreach ($models as $index => $model) {
        expect($model->fresh()->getAttributes())->toBe($snapshots[$index]);
    }
})->with(['canonical', 'alias']);

test('an exception at source deletion rolls back consolidation aliases and references', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $source = Skill::factory()->create();
    $target = Skill::factory()->create();
    $targetLink = CandidateSkill::factory()->for($target)->create(['confidence' => '0.10']);
    $sourceLink = CandidateSkill::factory()->for($source)->create(['candidate_profile_id' => $targetLink->candidate_profile_id, 'confidence' => '0.90']);
    $step = RoadmapStep::factory()->for($source, 'targetSkill')->create();
    $models = [$source, $target, $sourceLink, $targetLink, $step];
    $snapshots = array_map(fn (Model $model): array => $model->refresh()->getAttributes(), $models);
    Exceptions::fake();
    Event::listen('eloquent.deleting: '.Skill::class, function (Skill $skill) use ($source): void {
        if ($skill->id === $source->id) {
            throw new RuntimeException('Intentional merge failure');
        }
    });

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/skills/'.$source->id.'/merge', ['target_skill_id' => $target->id])
        ->assertInternalServerError();

    foreach ($models as $index => $model) {
        expect($model->fresh()->getAttributes())->toBe($snapshots[$index]);
    }
    $this->assertDatabaseCount('skill_aliases', 0);
    Exceptions::assertReported(RuntimeException::class);
});
