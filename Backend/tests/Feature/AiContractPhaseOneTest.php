<?php

use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\CareerPreference;
use App\Models\Experience;
use App\Models\JobPost;
use App\Models\JobSkill;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

test('candidate skill AI metadata persists internally with decimal and JSON casts', function (string $confidence) {
    $skill = CandidateSkill::factory()->create();
    $skill->update(['confidence' => $confidence, 'evidence' => ['text' => 'Built Laravel applications', 'pages' => [1, 2]]]);

    $data = $skill->fresh()->toArray();

    expect($data['confidence'])->toBe($confidence);
    expect($data['evidence'])->toBe(['text' => 'Built Laravel applications', 'pages' => [1, 2]]);
})->with(['0.00', '0.85', '1.00']);

test('manual skill creation cannot spoof AI metadata', function () {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/skills', [
        'name' => 'Laravel', 'confidence' => '1.00', 'evidence' => ['text' => 'Spoofed'], 'source' => 'ai_suggested',
    ])->assertCreated()->assertJsonPath('data.source', 'manual');

    $this->assertDatabaseHas('candidate_skills', [
        'candidate_profile_id' => $profile->id, 'source' => 'manual', 'confidence' => null, 'evidence' => null,
    ]);
});

test('job skill required level persists without changing legacy attributes', function (string $level) {
    $skill = JobSkill::factory()->create(['is_required' => false, 'importance' => 3]);
    $skill->update(['required_level' => $level]);

    expect($skill->fresh()->only(['required_level', 'is_required', 'importance']))
        ->toBe(['required_level' => $level, 'is_required' => false, 'importance' => 3]);
})->with(['beginner', 'intermediate', 'advanced', 'expert']);

test('job post AI fields persist and serialize alongside legacy experience level', function () {
    $job = JobPost::factory()->create(['experience_level' => 'senior']);
    $job->update([
        'min_years_experience' => 0, 'max_years_experience' => 5,
        'responsibilities' => ['Build APIs', 'Review code'], 'canonical_role' => 'Backend Developer',
    ]);

    expect($job->fresh()->toArray())->toMatchArray([
        'min_years_experience' => 0, 'max_years_experience' => 5,
        'responsibilities' => ['Build APIs', 'Review code'], 'canonical_role' => 'Backend Developer', 'experience_level' => 'senior',
    ]);
});

test('experience technologies can be created and retrieved through candidate APIs', function () {
    $profile = CandidateProfile::factory()->create();
    $this->withToken(JWTAuth::fromUser($profile->user));

    $response = $this->postJson('/api/candidate/experiences', [
        'job_title' => 'Developer', 'company_name' => 'Company', 'technologies' => ['Laravel', 'React'],
    ])->assertCreated()->assertJsonPath('data.technologies', ['Laravel', 'React']);

    expect($profile->experiences()->firstOrFail()->technologies)->toBe(['Laravel', 'React']);
    $this->getJson('/api/candidate/experiences/'.$response->json('data.id'))
        ->assertOk()->assertJsonPath('data.technologies', ['Laravel', 'React']);
    $this->getJson('/api/candidate/experiences')->assertOk()->assertJsonPath('data.0.technologies', ['Laravel', 'React']);
    $this->getJson('/api/candidate/profile')->assertOk()->assertJsonPath('data.profile.experiences.0.technologies', ['Laravel', 'React']);
});

test('experience technologies can be replaced cleared or omitted on PATCH', function (array $patch, ?array $expected) {
    $experience = Experience::factory()->create(['technologies' => ['Laravel', 'React']]);

    $this->withToken(JWTAuth::fromUser($experience->candidateProfile->user))
        ->patchJson('/api/candidate/experiences/'.$experience->id, $patch)
        ->assertOk()->assertJsonPath('data.technologies', $expected);

    expect($experience->fresh()->technologies)->toBe($expected);
})->with([
    'replace' => [['technologies' => ['Vue']], ['Vue']],
    'empty' => [['technologies' => []], []],
    'null' => [['technologies' => null], null],
    'omit' => [['job_title' => 'Architect'], ['Laravel', 'React']],
]);

test('career preference lists can be created and retrieved without changing legacy fields', function () {
    $profile = CandidateProfile::factory()->create();
    $data = [
        'target_role' => 'Developer', 'job_type' => 'Full time', 'work_mode' => 'Remote',
        'target_roles' => ['Developer', 'Architect'], 'preferred_industries' => ['Education', 'Technology'],
    ];
    $this->withToken(JWTAuth::fromUser($profile->user));

    $this->patchJson('/api/candidate/career-preferences', $data)->assertOk()->assertJson(['data' => $data]);

    expect($profile->careerPreference()->firstOrFail()->only(array_keys($data)))->toBe($data);
    $this->getJson('/api/candidate/career-preferences')->assertOk()->assertJson(['data' => $data]);
    $this->getJson('/api/candidate/profile')->assertOk()->assertJson(['data' => ['profile' => ['careerPreference' => $data]]]);
});

test('career preference lists can be patched independently without clearing omitted fields', function (array $patch, ?array $roles, ?array $industries) {
    $preference = CareerPreference::factory()->create([
        'target_role' => 'Developer', 'job_type' => 'Full time', 'work_mode' => 'Remote',
        'target_roles' => ['Developer'], 'preferred_industries' => ['Technology'],
    ]);

    $this->withToken(JWTAuth::fromUser($preference->candidateProfile->user))
        ->patchJson('/api/candidate/career-preferences', $patch)->assertOk()
        ->assertJsonPath('data.target_roles', $roles)->assertJsonPath('data.preferred_industries', $industries);

    expect($preference->fresh()->only(['target_roles', 'preferred_industries', 'target_role', 'job_type', 'work_mode']))
        ->toBe(['target_roles' => $roles, 'preferred_industries' => $industries,
            'target_role' => 'Developer', 'job_type' => 'Full time', 'work_mode' => 'Remote']);
})->with([
    'roles only' => [['target_roles' => ['Architect']], ['Architect'], ['Technology']],
    'industries only' => [['preferred_industries' => ['Education']], ['Developer'], ['Education']],
    'empty lists' => [['target_roles' => [], 'preferred_industries' => []], [], []],
    'null lists' => [['target_roles' => null, 'preferred_industries' => null], null, null],
    'omitted' => [['career_goal' => 'Lead a team'], ['Developer'], ['Technology']],
]);

test('candidate list extensions reject invalid values', function (string $field, mixed $value, string $errorSuffix) {
    $profile = CandidateProfile::factory()->create();
    $experience = Experience::factory()->for($profile)->create();
    $preference = CareerPreference::factory()->for($profile)->create();
    $url = $field === 'technologies' ? '/api/candidate/experiences/'.$experience->id : '/api/candidate/career-preferences';

    $this->withToken(JWTAuth::fromUser($profile->user))->patchJson($url, [$field => $value])
        ->assertUnprocessable()->assertJsonValidationErrors([$field.$errorSuffix]);

    expect($experience->fresh()->technologies)->toBeNull();
    expect($preference->fresh()->target_roles)->toBeNull();
    expect($preference->fresh()->preferred_industries)->toBeNull();
})->with(['technologies', 'target_roles', 'preferred_industries'])->with([
    'string' => ['Laravel', ''],
    'object' => [['key' => 'Laravel'], ''],
    'nested' => [[['Laravel']], '.0'],
    'number' => [[12], '.0'],
    'empty item' => [[''], '.0'],
    'long item' => [[str_repeat('a', 256)], '.0'],
]);

test('experience POST validates technology items', function () {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/experiences', [
        'job_title' => 'Developer', 'company_name' => 'Company', 'technologies' => [123],
    ])->assertUnprocessable()->assertJsonValidationErrors(['technologies.0']);

    $this->assertDatabaseCount('experiences', 0);
});

test('legacy model creation leaves all new fields null', function () {
    expect(CandidateSkill::factory()->create()->fresh()->only(['confidence', 'evidence']))
        ->toBe(['confidence' => null, 'evidence' => null]);
    expect(JobSkill::factory()->create()->fresh()->required_level)->toBeNull();
    expect(JobPost::factory()->create()->fresh()->only(['min_years_experience', 'max_years_experience', 'responsibilities', 'canonical_role']))
        ->toBe(['min_years_experience' => null, 'max_years_experience' => null, 'responsibilities' => null, 'canonical_role' => null]);
    expect(Experience::factory()->create()->fresh()->technologies)->toBeNull();
    expect(CareerPreference::factory()->create()->fresh()->only(['target_roles', 'preferred_industries']))
        ->toBe(['target_roles' => null, 'preferred_industries' => null]);
});

test('phase one migration preserves existing rows when adding nullable columns', function () {
    $migration = require database_path('migrations/2026_09_12_221750_add_ai_contract_phase_one_fields.php');
    $migration->down();
    $models = [
        CandidateSkill::factory()->create(), JobSkill::factory()->create(), JobPost::factory()->create(),
        Experience::factory()->create(), CareerPreference::factory()->create(),
    ];
    $snapshots = array_map(fn (Model $model): array => $model->refresh()->getAttributes(), $models);

    $migration->up();

    foreach ($models as $index => $model) {
        $attributes = $model->fresh()->getAttributes();
        foreach ($snapshots[$index] as $key => $value) {
            expect($attributes[$key])->toBe($value);
        }
        foreach (array_diff_key($attributes, $snapshots[$index]) as $value) {
            expect($value)->toBeNull();
        }
    }
});
