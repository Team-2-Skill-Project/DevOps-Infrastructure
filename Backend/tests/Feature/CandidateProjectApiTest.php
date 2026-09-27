<?php

use App\Models\CandidateProfile;
use App\Models\Project;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

dataset('project endpoints', [
    'index' => ['getJson', ''],
    'store' => ['postJson', ''],
    'show' => ['getJson', '/1'],
    'update' => ['patchJson', '/1'],
    'destroy' => ['deleteJson', '/1'],
]);

test('project endpoints return 401 without JWT', function (string $method, string $suffix) {
    $this->{$method}('/api/candidate/projects'.$suffix)
        ->assertUnauthorized()->assertExactJson(['message' => 'Unauthenticated.']);
})->with('project endpoints');

test('project endpoints return 401 for web sessions without JWT', function (string $method, string $suffix) {
    $user = User::factory()->create();

    $this->actingAs($user, 'web')->{$method}('/api/candidate/projects'.$suffix)->assertUnauthorized();
})->with('project endpoints');

test('project endpoints return 403 for non candidates', function (string $method, string $suffix, string $role) {
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))->{$method}('/api/candidate/projects'.$suffix)
        ->assertForbidden()->assertExactJson(['message' => 'Only candidates can access this profile.']);

    $this->assertDatabaseCount('projects', 0);
})->with('project endpoints')->with(['admin', 'super_admin']);

test('project endpoints return 403 for inactive candidates with existing JWT', function (string $method, string $suffix) {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);
    $token = JWTAuth::fromUser($user);
    $user->forceFill(['is_active' => false])->save();

    $this->withToken($token)->{$method}('/api/candidate/projects'.$suffix)
        ->assertForbidden()->assertExactJson(['message' => 'Your account is inactive.']);

    $this->assertDatabaseCount('projects', 0);
})->with('project endpoints');

test('project endpoints return 404 without creating a missing profile', function (string $method, string $suffix) {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))->{$method}('/api/candidate/projects'.$suffix)
        ->assertNotFound()->assertExactJson(['message' => 'Candidate profile not found.']);

    $this->assertDatabaseCount('candidate_profiles', 0);
    $this->assertDatabaseCount('projects', 0);
})->with('project endpoints');

test('project collection returns an empty array', function () {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->getJson('/api/candidate/projects')
        ->assertOk()->assertExactJson(['data' => []]);

    $this->assertDatabaseCount('projects', 0);
});

test('project collection includes only owned records ordered by start date then id descending', function () {
    $profile = CandidateProfile::factory()->create();
    $undated = Project::factory()->for($profile)->create(['start_date' => null]);
    $older = Project::factory()->for($profile)->create(['start_date' => '2018-09-01']);
    $newer = Project::factory()->for($profile)->create(['start_date' => '2022-09-01']);
    $tie = Project::factory()->for($profile)->create(['start_date' => '2022-09-01']);
    $other = Project::factory()->create();

    $response = $this->withToken(JWTAuth::fromUser($profile->user))
        ->getJson('/api/candidate/projects?candidate_profile_id='.$other->candidate_profile_id)
        ->assertOk()->assertJsonCount(4, 'data')->assertJsonMissingPath('data.0.candidate_profile_id');

    expect(array_column($response->json('data'), 'id'))->toBe([$tie->id, $newer->id, $older->id, $undated->id]);
});

test('a candidate can create a project with all supported fields', function () {
    $profile = CandidateProfile::factory()->create();
    $data = [
        'name' => 'Portfolio', 'description' => 'A developer portfolio', 'technologies' => ['Laravel', 'React'],
        'project_url' => 'https://example.com', 'github_url' => 'https://github.com/example/portfolio',
        'start_date' => '2023-01-01', 'end_date' => '2023-06-01', 'source' => 'manual',
    ];

    $response = $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/projects', $data)
        ->assertCreated()->assertJson(['data' => $data]);

    $saved = $profile->projects()->firstOrFail();
    expect($saved->id)->toBe($response->json('data.id'));
    expect($saved->technologies)->toBe(['Laravel', 'React']);
    expect($saved->start_date->toDateString())->toBe('2023-01-01');
    expect($saved->end_date->toDateString())->toBe('2023-06-01');
    $this->assertDatabaseHas('projects', [
        'candidate_profile_id' => $profile->id, 'name' => 'Portfolio', 'description' => 'A developer portfolio',
        'project_url' => 'https://example.com', 'github_url' => 'https://github.com/example/portfolio', 'source' => 'manual',
    ]);
    $this->assertDatabaseCount('projects', 1);
});

test('POST accepts only the required project name', function () {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/projects', ['name' => 'Portfolio'])
        ->assertCreated()->assertJsonPath('data.technologies', null)->assertJsonPath('data.end_date', null);

    $this->assertDatabaseHas('projects', ['candidate_profile_id' => $profile->id, 'name' => 'Portfolio', 'technologies' => null]);
});

test('POST requires a project name', function () {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/projects')
        ->assertUnprocessable()->assertJsonValidationErrors(['name']);

    $this->assertDatabaseCount('projects', 0);
});

test('a candidate can retrieve their own project with only public fields', function () {
    $project = Project::factory()->create(['name' => 'Portfolio', 'technologies' => ['Laravel'], 'start_date' => '2023-01-01']);

    $this->withToken(JWTAuth::fromUser($project->candidateProfile->user))->getJson('/api/candidate/projects/'.$project->id)
        ->assertOk()->assertExactJson(['data' => [
            'id' => $project->id, 'name' => 'Portfolio', 'description' => null, 'technologies' => ['Laravel'],
            'project_url' => null, 'github_url' => null, 'start_date' => '2023-01-01', 'end_date' => null, 'source' => 'manual',
        ]]);
});

test('another candidates project returns 404 and stays unchanged', function (string $method) {
    $profile = CandidateProfile::factory()->create();
    $other = Project::factory()->create();
    $attributes = $other->refresh()->getAttributes();

    $this->withToken(JWTAuth::fromUser($profile->user))->{$method}('/api/candidate/projects/'.$other->id, ['name' => null])
        ->assertNotFound()->assertExactJson(['message' => 'Project not found.']);

    expect($other->fresh()->getAttributes())->toBe($attributes);
})->with(['getJson', 'patchJson', 'deleteJson']);

test('nonexistent project returns the same 404', function (string $method) {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->{$method}('/api/candidate/projects/999999')
        ->assertNotFound()->assertExactJson(['message' => 'Project not found.']);
})->with(['getJson', 'patchJson', 'deleteJson']);

test('partial project PATCH preserves every omitted field', function () {
    $project = Project::factory()->create([
        'name' => 'Portfolio', 'description' => 'My portfolio', 'technologies' => ['Laravel', 'React'],
        'project_url' => 'https://example.com', 'github_url' => 'https://github.com/example/portfolio',
        'start_date' => '2023-01-01', 'end_date' => '2023-06-01', 'source' => 'manual',
    ]);
    $attributes = $project->refresh()->getAttributes();

    $this->withToken(JWTAuth::fromUser($project->candidateProfile->user))
        ->patchJson('/api/candidate/projects/'.$project->id, ['name' => 'Updated Portfolio'])
        ->assertOk()->assertJsonPath('data.name', 'Updated Portfolio');

    $saved = $project->fresh()->getAttributes();
    unset($saved['updated_at'], $attributes['updated_at']);
    expect($saved)->toBe([...$attributes, 'name' => 'Updated Portfolio']);
    $this->assertDatabaseCount('projects', 1);
});

test('a candidate can delete only their own project', function () {
    $project = Project::factory()->create();
    $other = Project::factory()->create();

    $this->withToken(JWTAuth::fromUser($project->candidateProfile->user))
        ->deleteJson('/api/candidate/projects/'.$project->id)->assertNoContent();

    $this->assertModelMissing($project);
    $this->assertModelExists($other);
});

test('protected project fields are ignored on POST and PATCH', function (string $method) {
    $this->travelTo(now()->startOfSecond());
    $profile = CandidateProfile::factory()->create();
    $owned = $method === 'patchJson' ? Project::factory()->for($profile)->create() : null;
    $other = Project::factory()->create();
    $attributes = $other->refresh()->getAttributes();

    $response = $this->withToken(JWTAuth::fromUser($profile->user))
        ->{$method}('/api/candidate/projects'.($owned ? '/'.$owned->id : ''), [
            'name' => 'Portfolio', 'id' => $other->id, 'candidate_profile_id' => $other->candidate_profile_id,
            'created_at' => '2000-01-01', 'updated_at' => '2000-01-01', 'user_id' => $other->candidateProfile->user_id,
            'is_current' => true,
        ])->assertSuccessful()->assertJsonMissingPath('data.candidate_profile_id')->assertJsonMissingPath('data.created_at')
        ->assertJsonMissingPath('data.is_current');

    $saved = $profile->projects()->firstOrFail();
    expect($saved->id)->toBe($owned?->id ?? $response->json('data.id'))->not->toBe($other->id);
    expect($saved->name)->toBe('Portfolio');
    expect($saved->created_at->equalTo(now()))->toBeTrue();
    expect($saved->updated_at->equalTo(now()))->toBeTrue();
    expect($other->fresh()->getAttributes())->toBe($attributes);
    $this->assertDatabaseCount('projects', 2);
})->with(['postJson', 'patchJson']);

test('invalid project input returns 422 on POST and PATCH', function (string $method, array $data, string $error) {
    $project = Project::factory()->create();
    $attributes = $project->refresh()->getAttributes();

    $this->withToken(JWTAuth::fromUser($project->candidateProfile->user))
        ->{$method}('/api/candidate/projects'.($method === 'patchJson' ? '/'.$project->id : ''), ['name' => 'Portfolio', ...$data])
        ->assertUnprocessable()->assertJsonValidationErrors([$error]);

    expect($project->fresh()->getAttributes())->toBe($attributes);
    $this->assertDatabaseCount('projects', 1);
})->with(['postJson', 'patchJson'])->with([
    'null name' => [['name' => null], 'name'],
    'empty name' => [['name' => ''], 'name'],
    'invalid start date' => [['start_date' => 'not a date'], 'start_date'],
    'invalid end date' => [['end_date' => '2024-02-30'], 'end_date'],
    'technologies string' => [['technologies' => 'Laravel'], 'technologies'],
    'technologies object' => [['technologies' => ['backend' => 'Laravel']], 'technologies'],
    'nested technology' => [['technologies' => [['name' => 'Laravel']]], 'technologies.0'],
    'numeric technology' => [['technologies' => [42]], 'technologies.0'],
    'empty technology' => [['technologies' => ['']], 'technologies.0'],
    'long technology' => [['technologies' => [str_repeat('a', 256)]], 'technologies.0'],
    ...collect(['project_url', 'github_url'])->flatMap(fn (string $field): array => [
        $field.' invalid' => [[$field => 'not a URL'], $field],
        $field.' length' => [[$field => 'https://example.com/'.str_repeat('a', 256)], $field],
        $field.' type' => [[$field => []], $field],
    ])->all(),
    ...collect(['name' => 255, 'description' => 5000, 'source' => 255])->flatMap(fn (int $limit, string $field): array => [
        $field.' type' => [[$field => []], $field],
        $field.' length' => [[$field => str_repeat('a', $limit + 1)], $field],
    ])->all(),
]);

test('reversed project dates return 422 on POST', function () {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/projects', [
        'name' => 'Portfolio', 'start_date' => '2023-01-01', 'end_date' => '2022-01-01',
    ])->assertUnprocessable()->assertJsonPath('errors.end_date.0', 'The end date must be on or after the start date.');

    $this->assertDatabaseCount('projects', 0);
});

test('project PATCH compares submitted dates against stored dates', function (array $stored, array $patch) {
    $project = Project::factory()->create($stored);
    $attributes = $project->refresh()->getAttributes();

    $this->withToken(JWTAuth::fromUser($project->candidateProfile->user))
        ->patchJson('/api/candidate/projects/'.$project->id, $patch)
        ->assertUnprocessable()->assertJsonPath('errors.end_date.0', 'The end date must be on or after the start date.');

    expect($project->fresh()->getAttributes())->toBe($attributes);
})->with([
    'stored start' => [['start_date' => '2023-01-01'], ['end_date' => '2022-01-01']],
    'stored end' => [['end_date' => '2023-01-01'], ['start_date' => '2024-01-01']],
    'both submitted' => [[], ['start_date' => '2023-01-01', 'end_date' => '2022-01-01']],
]);

test('projects allow matching start and end dates', function () {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/projects', [
        'name' => 'Portfolio', 'start_date' => '2023-01-01', 'end_date' => '2023-01-01',
    ])->assertCreated()->assertJsonPath('data.start_date', '2023-01-01')->assertJsonPath('data.end_date', '2023-01-01');

    $this->assertDatabaseCount('projects', 1);
});

test('project PATCH can clear every nullable field', function () {
    $project = Project::factory()->create([
        'description' => 'Portfolio', 'technologies' => ['Laravel'], 'project_url' => 'https://example.com',
        'github_url' => 'https://github.com/example/portfolio', 'start_date' => '2023-01-01', 'end_date' => '2023-06-01',
    ]);
    $data = array_fill_keys(['description', 'technologies', 'project_url', 'github_url', 'start_date', 'end_date', 'source'], null);

    $this->withToken(JWTAuth::fromUser($project->candidateProfile->user))
        ->patchJson('/api/candidate/projects/'.$project->id, $data)->assertOk()->assertJson(['data' => $data]);

    $this->assertDatabaseHas('projects', ['id' => $project->id, ...$data]);
});

test('project PATCH replaces the technologies list including an empty list', function (array $technologies) {
    $project = Project::factory()->create(['technologies' => ['Laravel', 'React']]);

    $this->withToken(JWTAuth::fromUser($project->candidateProfile->user))
        ->patchJson('/api/candidate/projects/'.$project->id, ['technologies' => $technologies])
        ->assertOk()->assertJsonPath('data.technologies', $technologies);

    expect($project->fresh()->technologies)->toBe($technologies);
})->with(['replacement' => [['Vue']], 'empty' => [[]]]);
