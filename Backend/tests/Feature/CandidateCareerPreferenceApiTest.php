<?php

use App\Models\CandidateProfile;
use App\Models\CareerPreference;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

test('career preferences return 401 without JWT', function (string $method) {
    $this->{$method}('/api/candidate/career-preferences')
        ->assertUnauthorized()->assertExactJson(['message' => 'Unauthenticated.']);
})->with(['getJson', 'patchJson']);

test('career preferences return 401 for a web session without JWT', function (string $method) {
    $user = User::factory()->create();

    $this->actingAs($user, 'web')->{$method}('/api/candidate/career-preferences')->assertUnauthorized();
})->with(['getJson', 'patchJson']);

test('career preferences return 403 for non candidates', function (string $method, string $role) {
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))->{$method}('/api/candidate/career-preferences', ['target_role' => []])
        ->assertForbidden()->assertExactJson(['message' => 'Only candidates can access this profile.']);

    $this->assertDatabaseCount('career_preferences', 0);
})->with(['getJson', 'patchJson'])->with(['admin', 'super_admin']);

test('career preferences return 403 for inactive candidates with a previously issued JWT', function (string $method) {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);
    $token = JWTAuth::fromUser($user);
    $user->forceFill(['is_active' => false])->save();

    $this->withToken($token)->{$method}('/api/candidate/career-preferences', ['target_role' => []])
        ->assertForbidden()->assertExactJson(['message' => 'Your account is inactive.']);

    $this->assertDatabaseCount('career_preferences', 0);
})->with(['getJson', 'patchJson']);

test('career preferences return 404 without creating a missing profile', function (string $method) {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))->{$method}('/api/candidate/career-preferences')
        ->assertNotFound()->assertExactJson(['message' => 'Candidate profile not found.']);

    $this->assertDatabaseCount('candidate_profiles', 0);
    $this->assertDatabaseCount('career_preferences', 0);
})->with(['getJson', 'patchJson']);

test('GET returns null without creating missing career preferences', function () {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->getJson('/api/candidate/career-preferences')
        ->assertOk()->assertExactJson(['data' => null]);

    $this->assertDatabaseCount('career_preferences', 0);
});

test('GET returns only the JWT owners career preferences and public fields', function () {
    $preference = CareerPreference::factory()->create(['target_role' => 'Developer']);
    $other = CareerPreference::factory()->create(['target_role' => 'Designer']);

    $this->withToken(JWTAuth::fromUser($preference->candidateProfile->user))
        ->getJson('/api/candidate/career-preferences?candidate_profile_id='.$other->candidate_profile_id.'&id='.$other->id)
        ->assertOk()->assertExactJson(['data' => [
            'id' => $preference->id, 'target_role' => 'Developer', 'job_type' => null,
            'work_mode' => null, 'preferred_country' => null, 'preferred_city' => null,
            'experience_level' => null, 'career_goal' => null, 'open_to_relocation' => false,
            'target_roles' => null, 'preferred_industries' => null,
        ]]);
});

test('PATCH creates career preferences for an existing profile', function () {
    $profile = CandidateProfile::factory()->create();
    $data = [
        'target_role' => 'Developer', 'job_type' => 'Full time', 'work_mode' => 'Remote',
        'preferred_country' => 'Egypt', 'preferred_city' => 'Cairo',
        'experience_level' => 'Senior', 'career_goal' => 'Lead a team', 'open_to_relocation' => true,
    ];

    $this->withToken(JWTAuth::fromUser($profile->user))->patchJson('/api/candidate/career-preferences', $data)
        ->assertOk()->assertJson(['data' => $data]);

    $this->assertDatabaseHas('career_preferences', ['candidate_profile_id' => $profile->id, ...$data]);
    $this->assertDatabaseCount('career_preferences', 1);
    $this->assertDatabaseCount('candidate_profiles', 1);
});

test('an empty PATCH creates preferences with database defaults', function () {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))->patchJson('/api/candidate/career-preferences')
        ->assertOk()->assertJsonPath('data.open_to_relocation', false)->assertJsonPath('data.target_role', null);

    $this->assertDatabaseHas('career_preferences', [
        'candidate_profile_id' => $profile->id, 'open_to_relocation' => false, 'target_role' => null,
    ]);
});

test('PATCH updates existing career preferences and preserves omitted fields', function () {
    $preference = CareerPreference::factory()->create([
        'target_role' => 'Developer', 'job_type' => 'Full time', 'work_mode' => 'Remote',
        'preferred_country' => 'Egypt', 'preferred_city' => 'Cairo',
        'experience_level' => 'Senior', 'career_goal' => 'Lead a team', 'open_to_relocation' => true,
    ]);

    $this->withToken(JWTAuth::fromUser($preference->candidateProfile->user))
        ->patchJson('/api/candidate/career-preferences', ['target_role' => 'Architect', 'open_to_relocation' => false])
        ->assertOk()->assertExactJson(['data' => [
            'id' => $preference->id, 'target_role' => 'Architect', 'job_type' => 'Full time',
            'work_mode' => 'Remote', 'preferred_country' => 'Egypt', 'preferred_city' => 'Cairo',
            'experience_level' => 'Senior', 'career_goal' => 'Lead a team', 'open_to_relocation' => false,
            'target_roles' => null, 'preferred_industries' => null,
        ]]);

    $this->assertDatabaseHas('career_preferences', [
        'id' => $preference->id, 'candidate_profile_id' => $preference->candidate_profile_id,
        'target_role' => 'Architect', 'job_type' => 'Full time', 'work_mode' => 'Remote',
        'preferred_country' => 'Egypt', 'preferred_city' => 'Cairo',
        'experience_level' => 'Senior', 'career_goal' => 'Lead a team', 'open_to_relocation' => false,
        'target_roles' => null, 'preferred_industries' => null,
    ]);
    $this->assertDatabaseCount('career_preferences', 1);
});

test('PATCH can clear nullable career preference fields', function () {
    $data = array_fill_keys(['target_role', 'job_type', 'work_mode', 'preferred_country', 'preferred_city', 'experience_level', 'career_goal'], 'Existing value');
    $preference = CareerPreference::factory()->create($data);
    $cleared = array_fill_keys(array_keys($data), null);

    $this->withToken(JWTAuth::fromUser($preference->candidateProfile->user))
        ->patchJson('/api/candidate/career-preferences', $cleared)
        ->assertOk()->assertJson(['data' => $cleared]);

    $this->assertDatabaseHas('career_preferences', ['id' => $preference->id, ...$cleared]);
});

test('PATCH ignores protected fields on creation and update', function (bool $existing) {
    $this->travelTo(now()->startOfSecond());
    $profile = CandidateProfile::factory()->create();
    $preference = $existing ? CareerPreference::factory()->for($profile)->create() : null;
    $other = CareerPreference::factory()->create(['target_role' => 'Other candidate']);
    $otherAttributes = $other->refresh()->getAttributes();

    $response = $this->withToken(JWTAuth::fromUser($profile->user))->patchJson('/api/candidate/career-preferences', [
        'target_role' => 'Architect', 'candidate_profile_id' => $other->candidate_profile_id,
        'id' => $other->id, 'user_id' => $other->candidateProfile->user_id,
        'created_at' => '2000-01-01', 'updated_at' => '2000-01-01', 'role' => 'admin',
    ])->assertOk()->assertJsonPath('data.target_role', 'Architect')
        ->assertJsonMissingPath('data.candidate_profile_id')->assertJsonMissingPath('data.created_at');

    $saved = $profile->careerPreference()->firstOrFail();
    expect($saved->id)->not->toBe($other->id);
    expect($saved->id)->toBe($preference?->id ?? $response->json('data.id'));
    expect($saved->created_at->equalTo(now()))->toBeTrue();
    expect($saved->updated_at->equalTo(now()))->toBeTrue();
    expect($other->fresh()->getAttributes())->toBe($otherAttributes);
    $this->assertDatabaseHas('career_preferences', ['id' => $saved->id, 'candidate_profile_id' => $profile->id, 'target_role' => 'Architect']);
    $this->assertDatabaseCount('career_preferences', 2);
})->with(['create' => false, 'update' => true]);

test('invalid career preference input returns 422 without changing stored data', function (string $field, mixed $value) {
    $preference = CareerPreference::factory()->create();
    $attributes = $preference->refresh()->getAttributes();

    $this->withToken(JWTAuth::fromUser($preference->candidateProfile->user))
        ->patchJson('/api/candidate/career-preferences', [$field => $value])
        ->assertUnprocessable()->assertJsonValidationErrors([$field]);

    expect($preference->fresh()->getAttributes())->toBe($attributes);
    $this->assertDatabaseCount('career_preferences', 1);
})->with([
    'boolean string' => ['open_to_relocation', 'yes'],
    'boolean null' => ['open_to_relocation', null],
    'boolean array' => ['open_to_relocation', []],
    ...collect([
        'target_role' => 255, 'job_type' => 100, 'work_mode' => 100,
        'preferred_country' => 255, 'preferred_city' => 255,
        'experience_level' => 100, 'career_goal' => 5000,
    ])->flatMap(fn (int $limit, string $field): array => [
        $field.' type' => [$field, []],
        $field.' length' => [$field, str_repeat('a', $limit + 1)],
    ])->all(),
]);
