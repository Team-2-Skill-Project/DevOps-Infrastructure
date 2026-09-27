<?php

use App\Models\Company;
use App\Models\JobPost;
use App\Models\JobSkill;
use App\Models\Skill;
use Illuminate\Database\QueryException;

it('preserves job company relationships and existing normalized fields', function () {
    $company = Company::factory()->create();
    $job = JobPost::factory()->for($company)->create([
        'job_type' => JobPost::TYPE_INTERNSHIP,
        'min_years_experience' => 1,
        'max_years_experience' => 3,
        'responsibilities' => ['Build features'],
        'canonical_role' => 'Backend Engineer',
    ]);

    expect($job->company->is($company))->toBeTrue()
        ->and($job->job_type)->toBe(JobPost::TYPE_INTERNSHIP)
        ->and($job->min_years_experience)->toBe(1)
        ->and($job->responsibilities)->toBe(['Build features'])
        ->and($job->canonical_role)->toBe('Backend Engineer');
});

it('casts active state and preserves relationships when inactive', function () {
    $job = JobPost::factory()->create(['is_active' => false]);
    $skill = Skill::factory()->create();
    $jobSkill = JobSkill::factory()->for($job)->for($skill)->create();

    expect($job->is_active)->toBeFalse()
        ->and($jobSkill->fresh()->jobPost->is($job))->toBeTrue()
        ->and($job->fresh()->jobSkills->first()->is($jobSkill))->toBeTrue();
});

it('determines expiration from nullable expiry timestamps', function (?string $expiresAt, bool $expired) {
    $job = JobPost::factory()->create(['expires_at' => $expiresAt]);

    expect($job->isExpired())->toBe($expired);
})->with([
    'no expiry' => [null, false],
    'future' => [now()->addDay()->toDateTimeString(), false],
    'past' => [now()->subDay()->toDateTimeString(), true],
]);

it('filters active and not expired jobs', function () {
    $active = JobPost::factory()->create(['is_active' => true, 'expires_at' => now()->addDay()]);
    JobPost::factory()->create(['is_active' => false, 'expires_at' => now()->addDay()]);
    JobPost::factory()->create(['is_active' => true, 'expires_at' => now()->subDay()]);

    expect(JobPost::active()->notExpired()->pluck('id')->all())->toBe([$active->id]);
});

it('persists source application and external URL fields', function () {
    $job = JobPost::factory()->create([
        'source' => JobPost::SOURCE_EXTERNAL_API,
        'external_url' => 'https://provider.example/jobs/123',
        'application_method' => JobPost::APPLICATION_EXTERNAL,
        'application_url' => 'https://provider.example/apply/123',
    ])->refresh();

    expect($job->source)->toBe(JobPost::SOURCE_EXTERNAL_API)
        ->and($job->external_url)->toBe('https://provider.example/jobs/123')
        ->and($job->application_url)->toBe('https://provider.example/apply/123')
        ->and($job->isExternallyApplied())->toBeTrue();
});

it('supports direct jobs and internal applications', function () {
    $job = JobPost::factory()->create([
        'source' => JobPost::SOURCE_DIRECT,
        'application_method' => JobPost::APPLICATION_INTERNAL,
        'application_url' => null,
    ]);

    expect($job->source)->toBe(JobPost::SOURCE_DIRECT)
        ->and($job->isExternallyApplied())->toBeFalse();
});

it('separates required and preferred skills while preserving pivot metadata', function () {
    $job = JobPost::factory()->create();
    $required = Skill::factory()->create();
    $preferred = Skill::factory()->create();
    $requiredLink = JobSkill::factory()->for($job)->for($required)->create([
        'is_required' => true, 'importance' => 5, 'required_level' => 'advanced',
    ]);
    JobSkill::factory()->for($job)->for($preferred)->create([
        'is_required' => false, 'importance' => 2, 'required_level' => 'beginner',
    ]);

    expect($job->skills)->toHaveCount(2)
        ->and($job->requiredSkills->modelKeys())->toBe([$required->id])
        ->and($job->preferredSkills->modelKeys())->toBe([$preferred->id])
        ->and($job->requiredSkills->first()->pivot->is_required)->toBe(1)
        ->and($job->requiredSkills->first()->pivot->importance)->toBe(5)
        ->and($job->requiredSkills->first()->pivot->required_level)->toBe('advanced')
        ->and($requiredLink->jobPost->is($job))->toBeTrue();
});

it('rejects duplicate skill pivots for one job', function () {
    $job = JobPost::factory()->create();
    $skill = Skill::factory()->create();
    JobSkill::factory()->for($job)->for($skill)->create();

    expect(fn () => JobSkill::factory()->for($job)->for($skill)->create())
        ->toThrow(QueryException::class);
});
