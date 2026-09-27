<?php

use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\CareerPreference;
use App\Models\Certificate;
use App\Models\CvDocument;
use App\Models\CvExtraction;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Language;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

test('unauthenticated requests cannot access the candidate profile API', function () {
    $this->getJson('/api/candidate/profile')
        ->assertUnauthorized()->assertExactJson(['message' => 'Unauthenticated.']);
});

test('a web session cannot access the candidate profile API without JWT', function () {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);

    $this->actingAs($user, 'web')->getJson('/api/candidate/profile')->assertUnauthorized();
});

test('an active candidate can retrieve a profile with empty relationships', function () {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);
    $profile = CandidateProfile::factory()->for($user)->create(['job_title' => 'Backend Developer']);

    $this->withToken(JWTAuth::fromUser($user))->getJson('/api/candidate/profile')
        ->assertOk()
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.is_active', true)
        ->assertJsonPath('data.profile_exists', true)
        ->assertJsonPath('data.profile.id', $profile->id)
        ->assertJsonPath('data.profile.job_title', 'Backend Developer')
        ->assertJsonPath('data.profile.careerPreference', null)
        ->assertJsonPath('data.profile.educations', [])
        ->assertJsonPath('data.profile.experiences', [])
        ->assertJsonPath('data.profile.projects', [])
        ->assertJsonPath('data.profile.certificates', [])
        ->assertJsonPath('data.profile.languages', [])
        ->assertJsonPath('data.profile.candidateSkills', [])
        ->assertJsonPath('data.profile.cvDocuments', []);
});

test('the candidate profile API returns the complete related profile without N plus one queries', function (int $count) {
    $user = User::factory()->withTwoFactor()->create([
        'role' => 'candidate', 'is_active' => true, 'google_id' => 'private-google-id',
    ]);
    $profile = CandidateProfile::factory()->for($user)
        ->has(CareerPreference::factory()->state(['target_role' => 'Backend Developer']), 'careerPreference')
        ->has(Education::factory()->count($count), 'educations')
        ->has(Experience::factory()->count($count), 'experiences')
        ->has(Project::factory()->count($count), 'projects')
        ->has(Certificate::factory()->count($count), 'certificates')
        ->has(Language::factory()->count($count), 'languages')
        ->has(CandidateSkill::factory()->count($count), 'candidateSkills')
        ->has(CvDocument::factory()->count($count)
            ->sequence(fn (Sequence $sequence): array => ['version' => $sequence->index + 1])
            ->has(CvExtraction::factory()->state([
                'status' => 'completed', 'confidence_score' => '0.9500',
                'raw_text' => 'Private parser input', 'extracted_data' => ['internal' => 'private'],
                'provider' => 'internal-provider', 'error_message' => 'Private diagnostic',
            ]), 'extractions'), 'cvDocuments')
        ->create();
    $profile->load(['educations', 'experiences', 'projects', 'certificates', 'languages', 'candidateSkills.skill', 'cvDocuments.extractions']);
    $token = JWTAuth::fromUser($user);
    $this->expectsDatabaseQueryCount(12);

    $response = $this->withToken($token)->getJson('/api/candidate/profile');

    $response->assertOk()
        ->assertJsonPath('data.profile.id', $profile->id)
        ->assertJsonPath('data.profile.careerPreference.target_role', 'Backend Developer')
        ->assertJsonPath('data.profile.candidateSkills.0.skill.id', $profile->candidateSkills->first()->skill->id)
        ->assertJsonPath('data.profile.candidateSkills.0.skill.name', $profile->candidateSkills->first()->skill->name)
        ->assertJsonPath('data.profile.cvDocuments.0.original_filename', $profile->cvDocuments->first()->original_filename)
        ->assertJsonPath('data.profile.cvDocuments.0.extractions.0.id', $profile->cvDocuments->first()->extractions->first()->id)
        ->assertJsonPath('data.profile.cvDocuments.0.extractions.0.confidence_score', '0.9500');

    foreach (['educations', 'experiences', 'projects', 'certificates', 'languages', 'candidateSkills', 'cvDocuments'] as $relation) {
        $response->assertJsonCount($count, 'data.profile.'.$relation);
        expect(array_column($response->json('data.profile.'.$relation), 'id'))
            ->toEqualCanonicalizing($profile->$relation->modelKeys());
    }

    foreach (['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'google_id', 'two_factor_confirmed_at'] as $field) {
        $response->assertJsonMissingPath('data.user.'.$field);
    }
    foreach (['storage_disk', 'storage_path', 'file_hash', 'failure_reason'] as $field) {
        $response->assertJsonMissingPath('data.profile.cvDocuments.0.'.$field);
    }
    foreach (['raw_text', 'extracted_data', 'provider', 'model', 'parser_version', 'error_message'] as $field) {
        $response->assertJsonMissingPath('data.profile.cvDocuments.0.extractions.0.'.$field);
    }
})->with(['one record per relationship' => 1, 'multiple records per relationship' => 3]);

test('a candidate without a profile receives a clean response and no profile is created', function () {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))->getJson('/api/candidate/profile')
        ->assertOk()
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.profile_exists', false)
        ->assertJsonPath('data.profile', null);

    $this->assertDatabaseCount('candidate_profiles', 0);
});

test('non candidates cannot access the candidate profile API', function (string $role) {
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);
    CandidateProfile::factory()->for($user)->create();

    $this->withToken(JWTAuth::fromUser($user))->getJson('/api/candidate/profile')
        ->assertForbidden()->assertExactJson(['message' => 'Only candidates can access this profile.']);
})->with(['admin', 'super_admin']);

test('an inactive candidate cannot access the profile even with a previously issued JWT', function () {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);
    CandidateProfile::factory()->for($user)->create();
    $token = JWTAuth::fromUser($user);
    $user->forceFill(['is_active' => false])->save();

    $this->withToken($token)->getJson('/api/candidate/profile')
        ->assertForbidden()->assertExactJson(['message' => 'Your account is inactive.']);
});

test('candidate profile API ignores requested identifiers and only returns the JWT owners profile', function () {
    $profile = CandidateProfile::factory()->create(['professional_summary' => 'My profile']);
    $otherProfile = CandidateProfile::factory()
        ->has(Education::factory(), 'educations')
        ->create(['professional_summary' => 'Someone elses profile']);

    $this->withToken(JWTAuth::fromUser($profile->user))
        ->getJson('/api/candidate/profile?user_id='.$otherProfile->user_id.'&candidate_profile_id='.$otherProfile->id)
        ->assertOk()
        ->assertJsonPath('data.user.id', $profile->user_id)
        ->assertJsonPath('data.profile.id', $profile->id)
        ->assertJsonPath('data.profile.professional_summary', 'My profile')
        ->assertJsonPath('data.profile.educations', []);
});

test('unauthenticated PATCH requests cannot access the candidate profile API', function () {
    $this->patchJson('/api/candidate/profile', ['job_title' => 'Backend Developer'])
        ->assertUnauthorized()->assertExactJson(['message' => 'Unauthenticated.']);
});

test('non candidates cannot update the candidate profile API', function (string $role) {
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))
        ->patchJson('/api/candidate/profile', ['job_title' => 'Backend Developer'])
        ->assertForbidden()->assertExactJson(['message' => 'Only candidates can access this profile.']);
})->with(['admin', 'super_admin']);

test('an inactive candidate cannot update the candidate profile API', function () {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => false]);

    $this->withToken(JWTAuth::fromUser($user))
        ->patchJson('/api/candidate/profile', ['job_title' => 'Backend Developer'])
        ->assertForbidden()->assertExactJson(['message' => 'Your account is inactive.']);
});

test('a candidate without a profile can create one through PATCH', function () {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))
        ->patchJson('/api/candidate/profile', [
            'date_of_birth' => '1990-05-20',
            'gender' => 'female',
            'job_title' => 'Backend Developer',
            'country' => 'Egypt',
            'state' => 'Cairo',
            'city' => 'Cairo',
            'military_status' => 'not_applicable',
            'github_url' => 'https://github.com/example',
            'linkedin_url' => 'https://www.linkedin.com/in/example',
            'professional_summary' => 'Builds reliable APIs.',
        ])
        ->assertOk()
        ->assertJsonPath('data.profile_exists', true)
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.profile.user_id', null)
        ->assertJsonPath('data.profile.job_title', 'Backend Developer')
        ->assertJsonPath('data.profile.professional_summary', 'Builds reliable APIs.');

    $this->assertDatabaseHas('candidate_profiles', [
        'user_id' => $user->id,
        'job_title' => 'Backend Developer',
    ]);
});

test('a candidate can update an existing profile through PATCH', function () {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);
    $profile = CandidateProfile::factory()->for($user)->create([
        'job_title' => 'Developer',
        'country' => 'Egypt',
    ]);

    $this->withToken(JWTAuth::fromUser($user))
        ->patchJson('/api/candidate/profile', ['job_title' => 'Senior Developer'])
        ->assertOk()
        ->assertJsonPath('data.profile_exists', true)
        ->assertJsonPath('data.profile.id', $profile->id)
        ->assertJsonPath('data.profile.job_title', 'Senior Developer');

    expect(CandidateProfile::query()->where('user_id', $user->id)->count())->toBe(1);
});

test('a PATCH preserves profile fields that are not submitted', function () {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);
    CandidateProfile::factory()->for($user)->create([
        'job_title' => 'Developer',
        'country' => 'Egypt',
        'professional_summary' => 'Existing summary.',
    ]);

    $this->withToken(JWTAuth::fromUser($user))
        ->patchJson('/api/candidate/profile', ['city' => 'Cairo'])
        ->assertOk()
        ->assertJsonPath('data.profile.job_title', 'Developer')
        ->assertJsonPath('data.profile.country', 'Egypt')
        ->assertJsonPath('data.profile.city', 'Cairo')
        ->assertJsonPath('data.profile.professional_summary', 'Existing summary.');
});

test('a PATCH rejects a date of birth that is today or in the future', function () {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))
        ->patchJson('/api/candidate/profile', ['date_of_birth' => now()->toDateString()])
        ->assertUnprocessable()->assertJsonValidationErrors(['date_of_birth']);
});

test('a PATCH rejects invalid profile URLs', function () {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))
        ->patchJson('/api/candidate/profile', [
            'github_url' => 'not-a-url',
            'linkedin_url' => 'also-not-a-url',
        ])
        ->assertUnprocessable()->assertJsonValidationErrors(['github_url', 'linkedin_url']);
});

test('protected profile fields cannot be mass assigned through PATCH', function () {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);
    $otherUser = User::factory()->create(['role' => 'candidate', 'is_active' => true]);
    $profile = CandidateProfile::factory()->for($user)->create(['profile_completed_at' => null]);

    $this->withToken(JWTAuth::fromUser($user))
        ->patchJson('/api/candidate/profile', [
            'id' => $profile->id + 100,
            'user_id' => $otherUser->id,
            'profile_completed_at' => now()->toISOString(),
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
            'role' => 'admin',
            'is_active' => false,
            'password' => 'compromised',
            'job_title' => 'Safe Update',
        ])
        ->assertOk()
        ->assertJsonPath('data.profile.id', $profile->id)
        ->assertJsonPath('data.profile.job_title', 'Safe Update')
        ->assertJsonPath('data.profile.profile_completed_at', null);

    $profile->refresh();
    expect($profile->user_id)->toBe($user->id)
        ->and($profile->profile_completed_at)->toBeNull();
    expect(CandidateProfile::query()->count())->toBe(1);
});
