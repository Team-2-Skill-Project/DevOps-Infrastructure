<?php

use App\Models\CandidateProfile;
use App\Models\Education;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

dataset('education endpoints', [
    'index' => ['getJson', ''],
    'store' => ['postJson', ''],
    'show' => ['getJson', '/1'],
    'update' => ['patchJson', '/1'],
    'destroy' => ['deleteJson', '/1'],
]);

test('education endpoints return 401 without JWT', function (string $method, string $suffix) {
    $this->{$method}('/api/candidate/educations'.$suffix)
        ->assertUnauthorized()->assertExactJson(['message' => 'Unauthenticated.']);
})->with('education endpoints');

test('education endpoints return 401 for web sessions without JWT', function (string $method, string $suffix) {
    $user = User::factory()->create();

    $this->actingAs($user, 'web')->{$method}('/api/candidate/educations'.$suffix)->assertUnauthorized();
})->with('education endpoints');

test('education endpoints return 403 for non candidates', function (string $method, string $suffix, string $role) {
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))->{$method}('/api/candidate/educations'.$suffix)
        ->assertForbidden()->assertExactJson(['message' => 'Only candidates can access this profile.']);

    $this->assertDatabaseCount('educations', 0);
})->with('education endpoints')->with(['admin', 'super_admin']);

test('education endpoints return 403 for inactive candidates with existing JWT', function (string $method, string $suffix) {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);
    $token = JWTAuth::fromUser($user);
    $user->forceFill(['is_active' => false])->save();

    $this->withToken($token)->{$method}('/api/candidate/educations'.$suffix)
        ->assertForbidden()->assertExactJson(['message' => 'Your account is inactive.']);

    $this->assertDatabaseCount('educations', 0);
})->with('education endpoints');

test('education endpoints return 404 without creating a missing profile', function (string $method, string $suffix) {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))->{$method}('/api/candidate/educations'.$suffix)
        ->assertNotFound()->assertExactJson(['message' => 'Candidate profile not found.']);

    $this->assertDatabaseCount('candidate_profiles', 0);
    $this->assertDatabaseCount('educations', 0);
})->with('education endpoints');

test('education collection returns an empty array', function () {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->getJson('/api/candidate/educations')
        ->assertOk()->assertExactJson(['data' => []]);

    $this->assertDatabaseCount('educations', 0);
});

test('education collection includes only owned records ordered by start date then id descending', function () {
    $profile = CandidateProfile::factory()->create();
    $undated = Education::factory()->for($profile)->create(['start_date' => null]);
    $older = Education::factory()->for($profile)->create(['start_date' => '2018-09-01']);
    $newer = Education::factory()->for($profile)->create(['start_date' => '2022-09-01']);
    $tie = Education::factory()->for($profile)->create(['start_date' => '2022-09-01']);
    $other = Education::factory()->create();

    $response = $this->withToken(JWTAuth::fromUser($profile->user))
        ->getJson('/api/candidate/educations?candidate_profile_id='.$other->candidate_profile_id)
        ->assertOk()->assertJsonCount(4, 'data')->assertJsonMissingPath('data.0.candidate_profile_id');

    expect(array_column($response->json('data'), 'id'))->toBe([$tie->id, $newer->id, $older->id, $undated->id]);
});

test('a candidate can create education with all supported fields', function () {
    $profile = CandidateProfile::factory()->create();
    $data = [
        'education_level' => 'University', 'institution' => 'Cairo University',
        'field_of_study' => 'Computer Science', 'degree' => 'Bachelor',
        'start_date' => '2018-09-01', 'end_date' => '2022-06-01', 'is_current' => false,
        'grade' => 'A', 'description' => 'Software engineering studies', 'source' => 'manual',
    ];

    $response = $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/educations', $data)
        ->assertCreated()->assertJson(['data' => $data]);

    $saved = $profile->educations()->firstOrFail();
    expect($saved->id)->toBe($response->json('data.id'));
    expect($saved->start_date->toDateString())->toBe('2018-09-01');
    expect($saved->end_date->toDateString())->toBe('2022-06-01');
    $this->assertDatabaseHas('educations', ['candidate_profile_id' => $profile->id, 'institution' => 'Cairo University', 'description' => 'Software engineering studies']);
    $this->assertDatabaseCount('educations', 1);
});

test('a candidate can retrieve their own education with only public fields', function () {
    $education = Education::factory()->create(['institution' => 'Cairo University', 'start_date' => '2020-09-01']);

    $this->withToken(JWTAuth::fromUser($education->candidateProfile->user))->getJson('/api/candidate/educations/'.$education->id)
        ->assertOk()->assertExactJson(['data' => [
            'id' => $education->id, 'education_level' => null, 'institution' => 'Cairo University',
            'field_of_study' => null, 'degree' => 'Bachelor', 'start_date' => '2020-09-01',
            'end_date' => null, 'is_current' => false, 'grade' => null, 'description' => null, 'source' => 'manual',
        ]]);
});

test('another candidates education returns 404 and stays unchanged', function (string $method) {
    $profile = CandidateProfile::factory()->create();
    $other = Education::factory()->create();
    $attributes = $other->refresh()->getAttributes();

    $this->withToken(JWTAuth::fromUser($profile->user))->{$method}('/api/candidate/educations/'.$other->id, ['institution' => null])
        ->assertNotFound()->assertExactJson(['message' => 'Education not found.']);

    expect($other->fresh()->getAttributes())->toBe($attributes);
})->with(['getJson', 'patchJson', 'deleteJson']);

test('nonexistent education returns the same 404', function (string $method) {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->{$method}('/api/candidate/educations/999999')
        ->assertNotFound()->assertExactJson(['message' => 'Education not found.']);
})->with(['getJson', 'patchJson', 'deleteJson']);

test('partial education PATCH preserves every omitted field', function () {
    $education = Education::factory()->create([
        'education_level' => 'University', 'institution' => 'Original University',
        'field_of_study' => 'Computing', 'degree' => 'Bachelor', 'start_date' => '2018-09-01',
        'end_date' => '2022-06-01', 'is_current' => false, 'grade' => 'A', 'description' => 'Studies', 'source' => 'manual',
    ]);
    $attributes = $education->refresh()->getAttributes();

    $this->withToken(JWTAuth::fromUser($education->candidateProfile->user))
        ->patchJson('/api/candidate/educations/'.$education->id, ['institution' => 'Updated University'])
        ->assertOk()->assertJsonPath('data.institution', 'Updated University');

    expect($education->fresh()->only(array_diff(array_keys($attributes), ['updated_at'])))
        ->toEqual([...$education->only(array_diff(array_keys($attributes), ['updated_at'])), 'institution' => 'Updated University']);
    $this->assertDatabaseCount('educations', 1);
});

test('a candidate can delete only their own education', function () {
    $education = Education::factory()->create();
    $other = Education::factory()->create();

    $this->withToken(JWTAuth::fromUser($education->candidateProfile->user))
        ->deleteJson('/api/candidate/educations/'.$education->id)->assertNoContent();

    $this->assertModelMissing($education);
    $this->assertModelExists($other);
});

test('protected education fields are ignored on POST and PATCH', function (string $method) {
    $this->travelTo(now()->startOfSecond());
    $profile = CandidateProfile::factory()->create();
    $owned = $method === 'patchJson' ? Education::factory()->for($profile)->create() : null;
    $other = Education::factory()->create();
    $attributes = $other->refresh()->getAttributes();

    $response = $this->withToken(JWTAuth::fromUser($profile->user))
        ->{$method}('/api/candidate/educations'.($owned ? '/'.$owned->id : ''), [
            'institution' => 'My University', 'id' => $other->id, 'candidate_profile_id' => $other->candidate_profile_id,
            'created_at' => '2000-01-01', 'updated_at' => '2000-01-01', 'user_id' => $other->candidateProfile->user_id,
        ])->assertSuccessful()->assertJsonMissingPath('data.candidate_profile_id')->assertJsonMissingPath('data.created_at');

    $saved = $profile->educations()->firstOrFail();
    expect($saved->id)->toBe($owned?->id ?? $response->json('data.id'))->not->toBe($other->id);
    expect($saved->institution)->toBe('My University');
    expect($saved->created_at->equalTo(now()))->toBeTrue();
    expect($saved->updated_at->equalTo(now()))->toBeTrue();
    expect($other->fresh()->getAttributes())->toBe($attributes);
    $this->assertDatabaseCount('educations', 2);
})->with(['postJson', 'patchJson']);

test('POST requires institution', function () {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/educations')
        ->assertUnprocessable()->assertJsonValidationErrors(['institution']);

    $this->assertDatabaseCount('educations', 0);
});

test('invalid education fields return 422 on POST and PATCH', function (string $method, string $field, mixed $value) {
    $profile = CandidateProfile::factory()->create();
    $education = Education::factory()->for($profile)->create();
    $attributes = $education->refresh()->getAttributes();

    $this->withToken(JWTAuth::fromUser($profile->user))
        ->{$method}('/api/candidate/educations'.($method === 'patchJson' ? '/'.$education->id : ''), ['institution' => 'University', $field => $value])
        ->assertUnprocessable()->assertJsonValidationErrors([$field]);

    expect($education->fresh()->getAttributes())->toBe($attributes);
    $this->assertDatabaseCount('educations', 1);
})->with(['postJson', 'patchJson'])->with([
    'null institution' => ['institution', null],
    'empty institution' => ['institution', ''],
    'start date' => ['start_date', 'not a date'],
    'end date' => ['end_date', '2024-02-30'],
    'boolean' => ['is_current', 'yes'],
    'null boolean' => ['is_current', null],
    ...collect([
        'education_level' => 100, 'institution' => 255, 'field_of_study' => 255,
        'degree' => 255, 'grade' => 100, 'description' => 5000, 'source' => 100,
    ])->flatMap(fn (int $limit, string $field): array => [
        $field.' type' => [$field, []],
        $field.' length' => [$field, str_repeat('a', $limit + 1)],
    ])->all(),
]);

test('invalid education date relationships return 422 on POST', function (array $data, string $message) {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/educations', ['institution' => 'University', ...$data])
        ->assertUnprocessable()->assertJsonValidationErrors(['end_date'])->assertJsonPath('errors.end_date.0', $message);

    $this->assertDatabaseCount('educations', 0);
})->with([
    'reversed dates' => [['start_date' => '2022-01-01', 'end_date' => '2021-01-01'], 'The end date must be on or after the start date.'],
    'current with end date' => [['is_current' => true, 'end_date' => '2022-01-01'], 'The end date must be null when education is current.'],
]);

test('education PATCH validates dates against omitted stored fields', function (array $stored, array $patch, string $message) {
    $education = Education::factory()->create($stored);
    $attributes = $education->refresh()->getAttributes();

    $this->withToken(JWTAuth::fromUser($education->candidateProfile->user))
        ->patchJson('/api/candidate/educations/'.$education->id, $patch)
        ->assertUnprocessable()->assertJsonValidationErrors(['end_date'])->assertJsonPath('errors.end_date.0', $message);

    expect($education->fresh()->getAttributes())->toBe($attributes);
})->with([
    'stored start' => [['start_date' => '2022-01-01'], ['end_date' => '2021-01-01'], 'The end date must be on or after the start date.'],
    'stored end' => [['end_date' => '2022-01-01'], ['start_date' => '2023-01-01'], 'The end date must be on or after the start date.'],
    'stored current' => [['is_current' => true], ['end_date' => '2022-01-01'], 'The end date must be null when education is current.'],
    'stored end becoming current' => [['end_date' => '2022-01-01'], ['is_current' => true], 'The end date must be null when education is current.'],
]);

test('valid education date combinations can be created', function (array $data) {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->postJson('/api/candidate/educations', ['institution' => 'University', ...$data])
        ->assertCreated()->assertJson(['data' => $data]);

    $this->assertDatabaseCount('educations', 1);
})->with([
    'same day' => [['start_date' => '2022-01-01', 'end_date' => '2022-01-01']],
    'current with null end' => [['is_current' => true, 'end_date' => null]],
    'current without end' => [['is_current' => true]],
]);

test('education PATCH can clear nullable fields and mark education current', function () {
    $education = Education::factory()->create(['start_date' => '2018-09-01', 'end_date' => '2022-06-01']);
    $data = array_fill_keys(['education_level', 'field_of_study', 'degree', 'start_date', 'end_date', 'grade', 'description', 'source'], null);
    $data['is_current'] = true;

    $this->withToken(JWTAuth::fromUser($education->candidateProfile->user))
        ->patchJson('/api/candidate/educations/'.$education->id, $data)
        ->assertOk()->assertJson(['data' => $data]);

    $this->assertDatabaseHas('educations', ['id' => $education->id, ...$data]);
});
