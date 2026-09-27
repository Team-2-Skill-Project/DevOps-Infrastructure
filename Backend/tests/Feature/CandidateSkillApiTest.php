<?php

use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\Skill;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

dataset('skill endpoints', [
    'index' => ['getJson', ''],
    'store' => ['postJson', ''],
    'destroy' => ['deleteJson', '/1'],
]);

test('skill endpoints return 401 without JWT', function (string $method, string $suffix) {
    $this->{$method}('/api/candidate/skills'.$suffix)
        ->assertUnauthorized()->assertExactJson(['message' => 'Unauthenticated.']);
})->with('skill endpoints');

test('skill endpoints return 401 for web sessions without JWT', function (string $method, string $suffix) {
    $user = User::factory()->create();

    $this->actingAs($user, 'web')->{$method}('/api/candidate/skills'.$suffix)->assertUnauthorized();
})->with('skill endpoints');

test('skill endpoints return 403 for non candidates', function (string $method, string $suffix, string $role) {
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))->{$method}('/api/candidate/skills'.$suffix)
        ->assertForbidden()->assertExactJson(['message' => 'Only candidates can access this profile.']);

    $this->assertDatabaseCount('candidate_skills', 0);
})->with('skill endpoints')->with(['admin', 'super_admin']);

test('skill endpoints return 403 for inactive candidates with existing JWT', function (string $method, string $suffix) {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);
    $token = JWTAuth::fromUser($user);
    $user->forceFill(['is_active' => false])->save();

    $this->withToken($token)->{$method}('/api/candidate/skills'.$suffix)
        ->assertForbidden()->assertExactJson(['message' => 'Your account is inactive.']);

    $this->assertDatabaseCount('candidate_skills', 0);
})->with('skill endpoints');

test('skill endpoints return 404 without creating a missing profile', function (string $method, string $suffix) {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))->{$method}('/api/candidate/skills'.$suffix)
        ->assertNotFound()->assertExactJson(['message' => 'Candidate profile not found.']);

    $this->assertDatabaseCount('candidate_profiles', 0);
    $this->assertDatabaseCount('candidate_skills', 0);
})->with('skill endpoints');

test('skill collection returns an empty array', function () {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->getJson('/api/candidate/skills')
        ->assertOk()->assertExactJson(['data' => []]);

    $this->assertDatabaseCount('candidate_skills', 0);
});

test('manual addition creates canonical skill and association with proficiency', function () {
    $profile = CandidateProfile::factory()->create();

    $response = $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/skills', [
        'name' => ' Laravel ', 'proficiency_level' => 'Advanced',
    ])->assertCreated()->assertJsonPath('data.source', 'manual')->assertJsonPath('data.proficiency_level', 'Advanced')
        ->assertJsonPath('data.skill.name', 'Laravel')->assertJsonPath('data.skill.normalized_name', 'laravel');

    $this->assertDatabaseHas('skills', ['id' => $response->json('data.skill_id'), 'name' => 'Laravel', 'normalized_name' => 'laravel']);
    $this->assertDatabaseHas('candidate_skills', [
        'id' => $response->json('data.id'), 'candidate_profile_id' => $profile->id,
        'skill_id' => $response->json('data.skill_id'), 'source' => 'manual', 'proficiency_level' => 'Advanced',
    ]);
    $this->assertDatabaseCount('skills', 1);
    $this->assertDatabaseCount('candidate_skills', 1);
});

test('manual additions cannot spoof source or protected IDs', function (string $source) {
    $this->travelTo(now()->startOfSecond());
    $profile = CandidateProfile::factory()->create();
    $other = CandidateSkill::factory()->create();
    $otherAttributes = $other->refresh()->getAttributes();
    $taxonomyAttributes = $other->skill->getAttributes();

    $response = $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/skills', [
        'name' => 'Laravel', 'source' => $source, 'candidate_profile_id' => $other->candidate_profile_id,
        'skill_id' => $other->skill_id, 'id' => $other->id, 'normalized_name' => 'spoofed', 'category' => 'spoofed',
        'created_at' => '2000-01-01', 'updated_at' => '2000-01-01',
    ])->assertCreated()->assertJsonPath('data.source', 'manual')->assertJsonPath('data.skill.normalized_name', 'laravel')
        ->assertJsonPath('data.skill.category', null)->assertJsonMissingPath('data.candidate_profile_id');

    $saved = $profile->candidateSkills()->firstOrFail();
    expect($saved->id)->toBe($response->json('data.id'))->not->toBe($other->id);
    expect($saved->skill_id)->not->toBe($other->skill_id);
    expect($saved->created_at->equalTo(now()))->toBeTrue();
    expect($saved->updated_at->equalTo(now()))->toBeTrue();
    expect($other->fresh()->getAttributes())->toBe($otherAttributes);
    expect($other->skill->fresh()->getAttributes())->toBe($taxonomyAttributes);
})->with(['cv_extracted', 'normalized', 'ai_suggested', 'arbitrary']);

test('case and whitespace variants reuse existing taxonomy without modifying it', function (string $name) {
    $profile = CandidateProfile::factory()->create();
    $skill = Skill::factory()->create(['name' => 'Laravel', 'normalized_name' => 'laravel', 'category' => 'Backend']);
    $attributes = $skill->refresh()->getAttributes();

    $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/skills', ['name' => $name])
        ->assertCreated()->assertJsonPath('data.skill_id', $skill->id)->assertJsonPath('data.skill.name', 'Laravel')
        ->assertJsonPath('data.proficiency_level', null);

    $this->assertDatabaseCount('skills', 1);
    $this->assertDatabaseHas('candidate_skills', ['candidate_profile_id' => $profile->id, 'skill_id' => $skill->id]);
    expect($skill->fresh()->getAttributes())->toBe($attributes);
})->with(['Laravel', ' laravel ', 'LARAVEL']);

test('duplicate additions return 422 without overwriting source or proficiency', function (string $source) {
    $skill = Skill::factory()->create(['name' => 'Laravel', 'normalized_name' => 'laravel']);
    $association = CandidateSkill::factory()->for($skill)->create(['source' => $source, 'proficiency_level' => 'Expert']);
    $attributes = $association->refresh()->getAttributes();

    $this->withToken(JWTAuth::fromUser($association->candidateProfile->user))
        ->postJson('/api/candidate/skills', ['name' => ' LARAVEL ', 'proficiency_level' => 'Beginner'])
        ->assertUnprocessable()->assertJsonPath('errors.name.0', 'This skill is already attached to your profile.');

    expect($association->fresh()->getAttributes())->toBe($attributes);
    $this->assertDatabaseCount('candidate_skills', 1);
    $this->assertDatabaseCount('skills', 1);
})->with(['manual', 'cv_extracted', 'normalized', 'ai_suggested']);

test('another candidate can attach the same canonical skill', function () {
    $profile = CandidateProfile::factory()->create();
    $skill = Skill::factory()->create(['name' => 'Laravel', 'normalized_name' => 'laravel']);
    $other = CandidateSkill::factory()->for($skill)->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/skills', ['name' => 'laravel'])
        ->assertCreated()->assertJsonPath('data.skill_id', $skill->id);

    $this->assertDatabaseHas('candidate_skills', ['candidate_profile_id' => $profile->id, 'skill_id' => $skill->id]);
    $this->assertModelExists($other);
    $this->assertDatabaseCount('skills', 1);
    $this->assertDatabaseCount('candidate_skills', 2);
});

test('normalization preserves punctuation and internal whitespace in distinct skills', function (string $name, string $normalized) {
    $profile = CandidateProfile::factory()->create();
    Skill::factory()->create(['name' => 'C', 'normalized_name' => 'c']);

    $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/skills', ['name' => $name])
        ->assertCreated()->assertJsonPath('data.skill.normalized_name', $normalized);

    $this->assertDatabaseCount('skills', 2);
})->with([['C++', 'c++'], ['C#', 'c#'], ['Visual  Basic', 'visual  basic']]);

test('GET returns only owned associations with unchanged sources and canonical data', function () {
    $profile = CandidateProfile::factory()->create();
    $expected = [];
    foreach (['manual', 'cv_extracted', 'normalized', 'ai_suggested'] as $source) {
        $skill = Skill::factory()->create(['category' => 'Backend']);
        $association = CandidateSkill::factory()->for($profile)->for($skill)->create(['source' => $source, 'proficiency_level' => 'Advanced']);
        $expected[] = [
            'id' => $association->id, 'skill_id' => $skill->id, 'source' => $source, 'proficiency_level' => 'Advanced',
            'skill' => $skill->only(['id', 'name', 'normalized_name', 'category']),
        ];
    }
    $other = CandidateSkill::factory()->create();
    $token = JWTAuth::fromUser($profile->user);
    $this->expectsDatabaseQueryCount(4);

    $this->withToken($token)->getJson('/api/candidate/skills?candidate_profile_id='.$other->candidate_profile_id)
        ->assertOk()->assertExactJson(['data' => array_reverse($expected)]);
});

test('removing an owned skill preserves shared taxonomy and other associations', function () {
    $association = CandidateSkill::factory()->create();
    $skill = $association->skill;
    $other = CandidateSkill::factory()->for($skill)->create();

    $this->withToken(JWTAuth::fromUser($association->candidateProfile->user))
        ->deleteJson('/api/candidate/skills/'.$association->id)->assertNoContent();

    $this->assertModelMissing($association);
    $this->assertModelExists($skill);
    $this->assertModelExists($other);
});

test('deleting another candidates association returns 404', function () {
    $profile = CandidateProfile::factory()->create();
    $other = CandidateSkill::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->deleteJson('/api/candidate/skills/'.$other->id)
        ->assertNotFound()->assertExactJson(['message' => 'Candidate skill not found.']);

    $this->assertModelExists($other);
    $this->assertModelExists($other->skill);
});

test('deleting a nonexistent association returns 404', function () {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->deleteJson('/api/candidate/skills/999999')
        ->assertNotFound()->assertExactJson(['message' => 'Candidate skill not found.']);
});

test('invalid manual skill input returns 422 without creating records', function (array $data, string $field) {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/skills', $data)
        ->assertUnprocessable()->assertJsonValidationErrors([$field]);

    $this->assertDatabaseCount('skills', 0);
    $this->assertDatabaseCount('candidate_skills', 0);
})->with([
    'missing name' => [[], 'name'],
    'null name' => [['name' => null], 'name'],
    'blank name' => [['name' => '   '], 'name'],
    'array name' => [['name' => []], 'name'],
    'long name' => [['name' => str_repeat('a', 256)], 'name'],
    'array proficiency' => [['name' => 'Laravel', 'proficiency_level' => []], 'proficiency_level'],
    'long proficiency' => [['name' => 'Laravel', 'proficiency_level' => str_repeat('a', 256)], 'proficiency_level'],
]);

test('manual skill accepts null proficiency', function () {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/skills', ['name' => 'Laravel', 'proficiency_level' => null])
        ->assertCreated()->assertJsonPath('data.proficiency_level', null);

    $this->assertDatabaseHas('candidate_skills', ['candidate_profile_id' => $profile->id, 'proficiency_level' => null]);
});
