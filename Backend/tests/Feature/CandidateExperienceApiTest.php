<?php

use App\Models\CandidateProfile;
use App\Models\Experience;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

dataset('experience endpoints', [
    'index' => ['getJson', ''],
    'store' => ['postJson', ''],
    'show' => ['getJson', '/1'],
    'update' => ['patchJson', '/1'],
    'destroy' => ['deleteJson', '/1'],
]);

test('experience endpoints return 401 without JWT', function (string $method, string $suffix) {
    $this->{$method}('/api/candidate/experiences'.$suffix)
        ->assertUnauthorized()->assertExactJson(['message' => 'Unauthenticated.']);
})->with('experience endpoints');

test('experience endpoints return 401 for web sessions without JWT', function (string $method, string $suffix) {
    $user = User::factory()->create();

    $this->actingAs($user, 'web')->{$method}('/api/candidate/experiences'.$suffix)->assertUnauthorized();
})->with('experience endpoints');

test('experience endpoints return 403 for non candidates', function (string $method, string $suffix, string $role) {
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))->{$method}('/api/candidate/experiences'.$suffix)
        ->assertForbidden()->assertExactJson(['message' => 'Only candidates can access this profile.']);

    $this->assertDatabaseCount('experiences', 0);
})->with('experience endpoints')->with(['admin', 'super_admin']);

test('experience endpoints return 403 for inactive candidates with existing JWT', function (string $method, string $suffix) {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);
    $token = JWTAuth::fromUser($user);
    $user->forceFill(['is_active' => false])->save();

    $this->withToken($token)->{$method}('/api/candidate/experiences'.$suffix)
        ->assertForbidden()->assertExactJson(['message' => 'Your account is inactive.']);

    $this->assertDatabaseCount('experiences', 0);
})->with('experience endpoints');

test('experience endpoints return 404 without creating a missing profile', function (string $method, string $suffix) {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))->{$method}('/api/candidate/experiences'.$suffix)
        ->assertNotFound()->assertExactJson(['message' => 'Candidate profile not found.']);

    $this->assertDatabaseCount('candidate_profiles', 0);
    $this->assertDatabaseCount('experiences', 0);
})->with('experience endpoints');

test('experience collection returns an empty array', function () {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->getJson('/api/candidate/experiences')
        ->assertOk()->assertExactJson(['data' => []]);

    $this->assertDatabaseCount('experiences', 0);
});

test('experience collection includes only owned records ordered by start date then id descending', function () {
    $profile = CandidateProfile::factory()->create();
    $undated = Experience::factory()->for($profile)->create(['start_date' => null]);
    $older = Experience::factory()->for($profile)->create(['start_date' => '2018-09-01']);
    $newer = Experience::factory()->for($profile)->create(['start_date' => '2022-09-01']);
    $tie = Experience::factory()->for($profile)->create(['start_date' => '2022-09-01']);
    $other = Experience::factory()->create();

    $response = $this->withToken(JWTAuth::fromUser($profile->user))
        ->getJson('/api/candidate/experiences?candidate_profile_id='.$other->candidate_profile_id)
        ->assertOk()->assertJsonCount(4, 'data')->assertJsonMissingPath('data.0.candidate_profile_id');

    expect(array_column($response->json('data'), 'id'))->toBe([$tie->id, $newer->id, $older->id, $undated->id]);
});

test('a candidate can create experience with all supported fields', function () {
    $profile = CandidateProfile::factory()->create();
    $data = [
        'employment_type' => 'Full time', 'company_name' => 'Cairo Company',
        'country' => 'Egypt', 'job_title' => 'Developer',
        'start_date' => '2018-09-01', 'end_date' => '2022-06-01', 'is_current' => false,
        'city' => 'Cairo', 'description' => 'Built backend services', 'source' => 'manual',
    ];

    $response = $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/experiences', $data)
        ->assertCreated()->assertJson(['data' => $data]);

    $saved = $profile->experiences()->firstOrFail();
    expect($saved->id)->toBe($response->json('data.id'));
    expect($saved->start_date->toDateString())->toBe('2018-09-01');
    expect($saved->end_date->toDateString())->toBe('2022-06-01');
    $this->assertDatabaseHas('experiences', ['candidate_profile_id' => $profile->id, 'company_name' => 'Cairo Company', 'description' => 'Built backend services']);
    $this->assertDatabaseCount('experiences', 1);
});

test('a candidate can retrieve their own experience with only public fields', function () {
    $experience = Experience::factory()->create(['company_name' => 'Cairo Company', 'job_title' => 'Developer', 'start_date' => '2020-09-01']);

    $this->withToken(JWTAuth::fromUser($experience->candidateProfile->user))->getJson('/api/candidate/experiences/'.$experience->id)
        ->assertOk()->assertExactJson(['data' => [
            'id' => $experience->id, 'employment_type' => null, 'company_name' => 'Cairo Company',
            'country' => null, 'job_title' => 'Developer', 'start_date' => '2020-09-01',
            'end_date' => null, 'is_current' => false, 'city' => null, 'description' => null, 'source' => 'manual', 'technologies' => null,
        ]]);
});

test('another candidates experience returns 404 and stays unchanged', function (string $method) {
    $profile = CandidateProfile::factory()->create();
    $other = Experience::factory()->create();
    $attributes = $other->refresh()->getAttributes();

    $this->withToken(JWTAuth::fromUser($profile->user))->{$method}('/api/candidate/experiences/'.$other->id, ['company_name' => null])
        ->assertNotFound()->assertExactJson(['message' => 'Experience not found.']);

    expect($other->fresh()->getAttributes())->toBe($attributes);
})->with(['getJson', 'patchJson', 'deleteJson']);

test('nonexistent experience returns the same 404', function (string $method) {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->{$method}('/api/candidate/experiences/999999')
        ->assertNotFound()->assertExactJson(['message' => 'Experience not found.']);
})->with(['getJson', 'patchJson', 'deleteJson']);

test('partial experience PATCH preserves every omitted field', function () {
    $experience = Experience::factory()->create([
        'employment_type' => 'Full time', 'company_name' => 'Original Company',
        'country' => 'Egypt', 'job_title' => 'Developer', 'start_date' => '2018-09-01',
        'end_date' => '2022-06-01', 'is_current' => false, 'city' => 'Cairo', 'description' => 'Built APIs', 'source' => 'manual',
    ]);
    $attributes = $experience->refresh()->getAttributes();

    $this->withToken(JWTAuth::fromUser($experience->candidateProfile->user))
        ->patchJson('/api/candidate/experiences/'.$experience->id, ['company_name' => 'Updated Company'])
        ->assertOk()->assertJsonPath('data.company_name', 'Updated Company');

    expect($experience->fresh()->only(array_diff(array_keys($attributes), ['updated_at'])))
        ->toEqual([...$experience->only(array_diff(array_keys($attributes), ['updated_at'])), 'company_name' => 'Updated Company']);
    $this->assertDatabaseCount('experiences', 1);
});

test('a candidate can delete only their own experience', function () {
    $experience = Experience::factory()->create();
    $other = Experience::factory()->create();

    $this->withToken(JWTAuth::fromUser($experience->candidateProfile->user))
        ->deleteJson('/api/candidate/experiences/'.$experience->id)->assertNoContent();

    $this->assertModelMissing($experience);
    $this->assertModelExists($other);
});

test('protected experience fields are ignored on POST and PATCH', function (string $method) {
    $this->travelTo(now()->startOfSecond());
    $profile = CandidateProfile::factory()->create();
    $owned = $method === 'patchJson' ? Experience::factory()->for($profile)->create() : null;
    $other = Experience::factory()->create();
    $attributes = $other->refresh()->getAttributes();

    $response = $this->withToken(JWTAuth::fromUser($profile->user))
        ->{$method}('/api/candidate/experiences'.($owned ? '/'.$owned->id : ''), [
            'company_name' => 'My Company', 'job_title' => 'Developer', 'id' => $other->id, 'candidate_profile_id' => $other->candidate_profile_id,
            'created_at' => '2000-01-01', 'updated_at' => '2000-01-01', 'user_id' => $other->candidateProfile->user_id,
        ])->assertSuccessful()->assertJsonMissingPath('data.candidate_profile_id')->assertJsonMissingPath('data.created_at');

    $saved = $profile->experiences()->firstOrFail();
    expect($saved->id)->toBe($owned?->id ?? $response->json('data.id'))->not->toBe($other->id);
    expect($saved->company_name)->toBe('My Company');
    expect($saved->created_at->equalTo(now()))->toBeTrue();
    expect($saved->updated_at->equalTo(now()))->toBeTrue();
    expect($other->fresh()->getAttributes())->toBe($attributes);
    $this->assertDatabaseCount('experiences', 2);
})->with(['postJson', 'patchJson']);

test('POST requires job title and company name', function () {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/experiences')
        ->assertUnprocessable()->assertJsonValidationErrors(['job_title', 'company_name']);

    $this->assertDatabaseCount('experiences', 0);
});

test('invalid experience fields return 422 on POST and PATCH', function (string $method, string $field, mixed $value) {
    $profile = CandidateProfile::factory()->create();
    $experience = Experience::factory()->for($profile)->create();
    $attributes = $experience->refresh()->getAttributes();

    $this->withToken(JWTAuth::fromUser($profile->user))
        ->{$method}('/api/candidate/experiences'.($method === 'patchJson' ? '/'.$experience->id : ''), ['job_title' => 'Developer', 'company_name' => 'Company', $field => $value])
        ->assertUnprocessable()->assertJsonValidationErrors([$field]);

    expect($experience->fresh()->getAttributes())->toBe($attributes);
    $this->assertDatabaseCount('experiences', 1);
})->with(['postJson', 'patchJson'])->with([
    'null job title' => ['job_title', null],
    'empty job title' => ['job_title', ''],
    'null company_name' => ['company_name', null],
    'empty company_name' => ['company_name', ''],
    'start date' => ['start_date', 'not a date'],
    'end date' => ['end_date', '2024-02-30'],
    'boolean' => ['is_current', 'yes'],
    'null boolean' => ['is_current', null],
    ...collect([
        'employment_type' => 255, 'company_name' => 255, 'country' => 255,
        'job_title' => 255, 'city' => 255, 'description' => 5000, 'source' => 255,
    ])->flatMap(fn (int $limit, string $field): array => [
        $field.' type' => [$field, []],
        $field.' length' => [$field, str_repeat('a', $limit + 1)],
    ])->all(),
]);

test('invalid experience date relationships return 422 on POST', function (array $data, string $message) {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/experiences', ['job_title' => 'Developer', 'company_name' => 'Company', ...$data])
        ->assertUnprocessable()->assertJsonValidationErrors(['end_date'])->assertJsonPath('errors.end_date.0', $message);

    $this->assertDatabaseCount('experiences', 0);
})->with([
    'reversed dates' => [['start_date' => '2022-01-01', 'end_date' => '2021-01-01'], 'The end date must be on or after the start date.'],
    'current with end date' => [['is_current' => true, 'end_date' => '2022-01-01'], 'The end date must be null when experience is current.'],
]);

test('experience PATCH validates dates against omitted stored fields', function (array $stored, array $patch, string $message) {
    $experience = Experience::factory()->create($stored);
    $attributes = $experience->refresh()->getAttributes();

    $this->withToken(JWTAuth::fromUser($experience->candidateProfile->user))
        ->patchJson('/api/candidate/experiences/'.$experience->id, $patch)
        ->assertUnprocessable()->assertJsonValidationErrors(['end_date'])->assertJsonPath('errors.end_date.0', $message);

    expect($experience->fresh()->getAttributes())->toBe($attributes);
})->with([
    'stored start' => [['start_date' => '2022-01-01'], ['end_date' => '2021-01-01'], 'The end date must be on or after the start date.'],
    'stored end' => [['end_date' => '2022-01-01'], ['start_date' => '2023-01-01'], 'The end date must be on or after the start date.'],
    'stored current' => [['is_current' => true], ['end_date' => '2022-01-01'], 'The end date must be null when experience is current.'],
    'stored end becoming current' => [['end_date' => '2022-01-01'], ['is_current' => true], 'The end date must be null when experience is current.'],
]);

test('valid experience date combinations can be created', function (array $data) {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/experiences', ['job_title' => 'Developer', 'company_name' => 'Company', ...$data])
        ->assertCreated()->assertJson(['data' => $data]);

    $this->assertDatabaseCount('experiences', 1);
})->with([
    'same day' => [['start_date' => '2022-01-01', 'end_date' => '2022-01-01']],
    'current with null end' => [['is_current' => true, 'end_date' => null]],
    'current without end' => [['is_current' => true]],
]);

test('experience PATCH can clear nullable fields and mark experience current', function () {
    $experience = Experience::factory()->create(['start_date' => '2018-09-01', 'end_date' => '2022-06-01']);
    $data = array_fill_keys(['employment_type', 'country', 'start_date', 'end_date', 'city', 'description', 'source'], null);
    $data['is_current'] = true;

    $this->withToken(JWTAuth::fromUser($experience->candidateProfile->user))
        ->patchJson('/api/candidate/experiences/'.$experience->id, $data)
        ->assertOk()->assertJson(['data' => $data]);

    $this->assertDatabaseHas('experiences', ['id' => $experience->id, ...$data]);
});

test('POST accepts only required experience fields and applies the database defaults', function () {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))
        ->postJson('/api/candidate/experiences', ['job_title' => 'Developer', 'company_name' => 'Company'])
        ->assertCreated()->assertJsonPath('data.is_current', false)->assertJsonPath('data.end_date', null);

    $this->assertDatabaseHas('experiences', [
        'candidate_profile_id' => $profile->id, 'job_title' => 'Developer',
        'company_name' => 'Company', 'is_current' => false, 'end_date' => null,
    ]);
});

test('experience PATCH can finish a current job without changing its start date', function () {
    $experience = Experience::factory()->create(['start_date' => '2020-01-01', 'is_current' => true]);

    $this->withToken(JWTAuth::fromUser($experience->candidateProfile->user))
        ->patchJson('/api/candidate/experiences/'.$experience->id, ['is_current' => false, 'end_date' => '2022-01-01'])
        ->assertOk()->assertJsonPath('data.is_current', false)
        ->assertJsonPath('data.start_date', '2020-01-01')->assertJsonPath('data.end_date', '2022-01-01');

    $saved = $experience->fresh();
    expect($saved->is_current)->toBeFalse();
    expect($saved->start_date->toDateString())->toBe('2020-01-01');
    expect($saved->end_date->toDateString())->toBe('2022-01-01');
});
