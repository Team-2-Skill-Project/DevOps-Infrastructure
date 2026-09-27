<?php

use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\CareerPreference;
use App\Models\Certificate;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Language;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use Carbon\CarbonInterface;
use Database\Seeders\CandidateProfileSeeder;
use Database\Seeders\SkillSeeder;
use Illuminate\Database\QueryException;

test('an account can have an incomplete profile without duplicating account information', function () {
    $user = User::factory()->create();
    expect($user->candidateProfile)->toBeNull();

    $profile = $user->candidateProfile()->create([])->refresh();

    expect($profile->user->is($user))->toBeTrue();
    expect($user->fresh()->candidateProfile->is($profile))->toBeTrue();
    expect($profile->date_of_birth)->toBeNull();
    expect($profile->profile_completed_at)->toBeNull();
    expect($profile->getAttributes())->not->toHaveKeys(['name', 'email', 'phone', 'age']);
});

test('personal profile fields persist with date and completion casts', function () {
    $profile = CandidateProfile::factory()->create();

    $profile->fill([
        'date_of_birth' => '1996-02-15',
        'gender' => 'Female',
        'job_title' => 'Backend Developer',
        'country' => 'Egypt',
        'state' => 'Cairo',
        'city' => 'Cairo',
        'github_url' => 'https://github.com/example',
        'linkedin_url' => 'https://linkedin.com/in/example',
        'military_status' => 'Not applicable',
        'professional_summary' => 'Builds accessible services.',
        'profile_completed_at' => '2026-09-08 10:30:00',
    ])->save();
    $profile->refresh();

    expect($profile->date_of_birth)->toBeInstanceOf(CarbonInterface::class);
    expect($profile->date_of_birth->toDateString())->toBe('1996-02-15');
    expect($profile->profile_completed_at)->toBeInstanceOf(CarbonInterface::class);
    expect($profile->profile_completed_at->format('Y-m-d H:i:s'))->toBe('2026-09-08 10:30:00');
    expect($profile->only(['gender', 'job_title', 'country', 'state', 'city', 'github_url', 'linkedin_url', 'military_status', 'professional_summary']))->toBe([
        'gender' => 'Female', 'job_title' => 'Backend Developer', 'country' => 'Egypt', 'state' => 'Cairo', 'city' => 'Cairo',
        'github_url' => 'https://github.com/example', 'linkedin_url' => 'https://linkedin.com/in/example',
        'military_status' => 'Not applicable', 'professional_summary' => 'Builds accessible services.',
    ]);
});

test('profile records retain their fields casts and owner', function (string $model, string $relation, array $attributes, array $dates) {
    $profile = CandidateProfile::factory()->create();
    $record = $model::factory()->for($profile)->create();

    $record->fill($attributes)->save();
    $record->refresh();

    expect($record->candidateProfile->is($profile))->toBeTrue();
    $related = $profile->$relation;
    expect($related instanceof CareerPreference ? $related->is($record) : $related->contains($record))->toBeTrue();
    foreach ($attributes as $field => $value) {
        if (in_array($field, $dates, true)) {
            expect($record->$field)->toBeInstanceOf(CarbonInterface::class);
            expect($record->$field->toDateString())->toBe($value);
        } else {
            expect($record->$field)->toBe($value);
        }
    }
})->with([
    'education' => [Education::class, 'educations', ['education_level' => 'University', 'institution' => 'Cairo University', 'field_of_study' => 'Computer Science', 'degree' => 'BSc', 'start_date' => '2015-09-01', 'end_date' => '2019-06-01', 'is_current' => false, 'grade' => 'A', 'description' => 'Software engineering.', 'source' => 'cv_extracted'], ['start_date', 'end_date']],
    'experience' => [Experience::class, 'experiences', ['job_title' => 'Developer', 'company_name' => 'Example', 'employment_type' => 'Full-time', 'country' => 'Egypt', 'city' => 'Cairo', 'start_date' => '2020-01-01', 'end_date' => null, 'is_current' => true, 'description' => 'Built services.', 'source' => 'manual'], ['start_date']],
    'project' => [Project::class, 'projects', ['name' => 'Portfolio', 'description' => 'Personal work.', 'technologies' => ['Laravel', 'React'], 'project_url' => 'https://example.com', 'github_url' => 'https://github.com/example/portfolio', 'start_date' => '2024-01-01', 'end_date' => '2024-06-01', 'source' => 'cv_extracted'], ['start_date', 'end_date']],
    'certificate' => [Certificate::class, 'certificates', ['name' => 'Cloud Certificate', 'issuer' => 'Example', 'issue_date' => '2024-01-01', 'expiration_date' => '2027-01-01', 'credential_id' => 'ABC-123', 'credential_url' => 'https://example.com/credential', 'source' => 'manual'], ['issue_date', 'expiration_date']],
    'language' => [Language::class, 'languages', ['language' => 'Arabic', 'proficiency_level' => 'Native', 'source' => 'cv_extracted'], []],
    'career preference' => [CareerPreference::class, 'careerPreference', ['target_role' => 'Backend Developer', 'job_type' => 'Full-time', 'work_mode' => 'Remote', 'preferred_country' => 'Egypt', 'preferred_city' => 'Cairo', 'experience_level' => 'Junior', 'career_goal' => 'Build reliable services.', 'open_to_relocation' => true], []],
]);

test('optional record fields may be omitted and booleans default to false', function () {
    $profile = CandidateProfile::factory()->create();

    $education = $profile->educations()->create([
        'institution' => 'Cairo University',
    ])->refresh();

    $experience = $profile->experiences()->create([
        'job_title' => 'Developer',
        'company_name' => 'Example',
    ])->refresh();

    $preference = $profile->careerPreference()->create([])->refresh();

    expect($education->source)->toBeNull();
    expect($education->is_current)->toBeFalse();
    expect($experience->is_current)->toBeFalse();
    expect($preference->open_to_relocation)->toBeFalse();
});

test('duplicate one to one records and taxonomy associations are rejected', function (string $model, string $key) {
    $record = $model::factory()->create();

    expect(fn () => $model::factory()->create($record->only(explode(',', $key))))->toThrow(QueryException::class);
})->with([
    'user profile' => [CandidateProfile::class, 'user_id'],
    'career preference' => [CareerPreference::class, 'candidate_profile_id'],
    'skill normalized name' => [Skill::class, 'normalized_name'],
    'candidate skill' => [CandidateSkill::class, 'candidate_profile_id,skill_id'],
]);

test('profile records cannot reference a missing owner', function (string $model, string $key) {
    expect(fn () => $model::factory()->create([$key => 999999]))->toThrow(QueryException::class);
})->with([
    [CandidateProfile::class, 'user_id'],
    [Education::class, 'candidate_profile_id'],
    [Experience::class, 'candidate_profile_id'],
    [Project::class, 'candidate_profile_id'],
    [Certificate::class, 'candidate_profile_id'],
    [Language::class, 'candidate_profile_id'],
    [CareerPreference::class, 'candidate_profile_id'],
    [CandidateSkill::class, 'candidate_profile_id'],
    [CandidateSkill::class, 'skill_id'],
]);

test('canonical skills expose provenance and proficiency in both directions', function (string $source) {
    $profile = CandidateProfile::factory()->create();
    $skill = Skill::factory()->create(['name' => 'Laravel', 'normalized_name' => 'laravel', 'category' => 'Backend']);

    $profile->skills()->attach($skill, ['source' => $source, 'proficiency_level' => 'Advanced']);

    $candidateSkill = $profile->candidateSkills()->sole();
    expect($candidateSkill->candidateProfile->is($profile))->toBeTrue();
    expect($candidateSkill->skill->is($skill))->toBeTrue();
    expect($skill->candidateSkills()->sole()->is($candidateSkill))->toBeTrue();
    $linkedSkill = $profile->skills()->sole();
    expect($linkedSkill->pivot->source)->toBe($source);
    expect($linkedSkill->pivot->proficiency_level)->toBe('Advanced');
    expect($linkedSkill->pivot->created_at)->toBeInstanceOf(CarbonInterface::class);
    expect($linkedSkill->pivot->updated_at)->toBeInstanceOf(CarbonInterface::class);
    expect($linkedSkill->pivot->id)->toBe($candidateSkill->id);
    expect($skill->candidateProfiles()->sole()->is($profile))->toBeTrue();
    expect($linkedSkill->only(['name', 'normalized_name', 'category']))->toBe(['name' => 'Laravel', 'normalized_name' => 'laravel', 'category' => 'Backend']);
})->with(['manual', 'cv_extracted', 'normalized', 'ai_suggested']);

test('removing a candidate skill preserves the taxonomy and another candidates association', function (bool $detach) {
    $skill = Skill::factory()->create();
    $link = CandidateSkill::factory()->for($skill)->create();
    $otherLink = CandidateSkill::factory()->for($skill)->create();

    if ($detach) {
        $link->candidateProfile->skills()->detach($skill);
    } else {
        $link->delete();
    }

    $this->assertModelMissing($link);
    $this->assertModelExists($skill);
    $this->assertModelExists($otherLink);
    expect($otherLink->candidateProfile->skills()->sole()->is($skill))->toBeTrue();
})->with([false, true]);

test('deleting an account or profile removes its dependent data while preserving shared skills', function (bool $deleteAccount) {
    $profile = CandidateProfile::factory()
        ->has(Education::factory(), 'educations')
        ->has(Experience::factory(), 'experiences')
        ->has(Project::factory(), 'projects')
        ->has(Certificate::factory(), 'certificates')
        ->has(Language::factory(), 'languages')
        ->has(CareerPreference::factory(), 'careerPreference')
        ->has(CandidateSkill::factory(), 'candidateSkills')
        ->create();
    $user = $profile->user;
    $records = [$profile->educations->sole(), $profile->experiences->sole(), $profile->projects->sole(), $profile->certificates->sole(), $profile->languages->sole(), $profile->careerPreference, $profile->candidateSkills->sole()];
    $skill = $profile->skills()->sole();
    $otherLink = CandidateSkill::factory()->for($skill)->create();

    ($deleteAccount ? $user : $profile)->delete();

    $this->assertModelMissing($profile);
    foreach ($records as $record) {
        $this->assertModelMissing($record);
    }
    $this->assertModelExists($skill);
    $this->assertModelExists($otherLink);
    $this->assertModelExists($otherLink->candidateProfile);
    if (! $deleteAccount) {
        $this->assertModelExists($user);
    }
})->with([false, true]);

test('deleting a taxonomy skill removes associations but preserves profiles', function () {
    $link = CandidateSkill::factory()->create();
    $profile = $link->candidateProfile;

    $link->skill->delete();

    $this->assertModelMissing($link);
    $this->assertModelExists($profile);
});

test('optional seeders create a complete sample graph and do not duplicate canonical skills', function () {
    $this->seed(CandidateProfileSeeder::class);
    $this->seed(SkillSeeder::class);

    $this->assertDatabaseCount('skills', 2);
    $profile = CandidateProfile::query()->sole();
    foreach (['educations', 'experiences', 'projects', 'certificates', 'languages', 'candidateSkills', 'skills'] as $relation) {
        expect($profile->$relation)->toHaveCount(1);
    }
    expect($profile->careerPreference)->toBeInstanceOf(CareerPreference::class);
});
