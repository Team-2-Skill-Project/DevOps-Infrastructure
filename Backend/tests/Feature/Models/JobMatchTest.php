<?php

use App\Models\CandidateProfile;
use App\Models\JobMatch;
use App\Models\JobPost;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;

it('persists structured AI output without flattening or changing detail fields', function () {
    $profile = CandidateProfile::factory()->create();
    $job = JobPost::factory()->create();
    $details = [
        'matched_skills' => [['skill_id' => 1, 'candidate_level' => 'advanced', 'required_level' => 'intermediate', 'evidence_strength' => 0.92]],
        'missing_skills' => [['skill_id' => 7, 'importance' => 'critical']],
        'weak_skills' => [['skill_id' => 3, 'reason' => 'Candidate level below required level']],
        'reasons' => [
            ['type' => 'strength', 'text' => 'Strong Laravel experience'],
            ['type' => 'gap', 'text' => 'Missing Docker experience'],
        ],
    ];

    $match = JobMatch::query()->create([
        'candidate_profile_id' => $profile->id, 'job_post_id' => $job->id,
        'score' => '87.25', 'confidence' => '0.92', 'calculated_at' => '2026-09-12 12:30:00', ...$details,
    ])->fresh();

    expect($match->only(array_keys($details)))->toBe($details);
    expect($match->toArray())->toMatchArray($details);
    expect($match->score)->toBe('87.25');
    expect($match->confidence)->toBe('0.92');
    expect($match->calculated_at)->toBeInstanceOf(CarbonInterface::class);
    expect($match->calculated_at->format('Y-m-d H:i:s'))->toBe('2026-09-12 12:30:00');
    $this->assertDatabaseHas('job_matches', ['id' => $match->id, 'candidate_profile_id' => $profile->id, 'job_post_id' => $job->id]);
});

it('preserves score and confidence at the supported range boundaries', function (string $score, string $confidence) {
    $match = JobMatch::factory()->create(['score' => $score, 'confidence' => $confidence])->fresh();

    expect($match->score)->toBe($score);
    expect($match->confidence)->toBe($confidence);
})->with([['0.00', '0.00'], ['100.00', '1.00']]);

it('allows all optional AI details to remain null', function () {
    $match = JobMatch::factory()->create()->fresh();

    expect($match->only(['matched_skills', 'missing_skills', 'weak_skills', 'reasons', 'confidence', 'calculated_at']))
        ->toBe(['matched_skills' => null, 'missing_skills' => null, 'weak_skills' => null,
            'reasons' => null, 'confidence' => null, 'calculated_at' => null]);
});

it('distinguishes empty AI arrays from absent detail fields', function () {
    $match = JobMatch::factory()->create(['matched_skills' => [], 'missing_skills' => [], 'weak_skills' => [], 'reasons' => []])->fresh();

    expect($match->only(['matched_skills', 'missing_skills', 'weak_skills', 'reasons']))
        ->toBe(['matched_skills' => [], 'missing_skills' => [], 'weak_skills' => [], 'reasons' => []]);
});

it('links matches to both parents and supports multiple matches for each parent', function () {
    $profile = CandidateProfile::factory()->create();
    $job = JobPost::factory()->create();
    $match = JobMatch::factory()->for($profile)->for($job)->create();
    $sameCandidate = JobMatch::factory()->for($profile)->create();
    $sameJob = JobMatch::factory()->for($job)->create();

    expect($match->candidateProfile->is($profile))->toBeTrue();
    expect($match->jobPost->is($job))->toBeTrue();
    expect($profile->jobMatches->modelKeys())->toEqualCanonicalizing([$match->id, $sameCandidate->id]);
    expect($job->jobMatches->modelKeys())->toEqualCanonicalizing([$match->id, $sameJob->id]);
});

it('rejects duplicate candidate and job pairs at the database boundary', function () {
    $match = JobMatch::factory()->create();

    expect(fn () => JobMatch::factory()->create([
        'candidate_profile_id' => $match->candidate_profile_id, 'job_post_id' => $match->job_post_id,
    ]))->toThrow(QueryException::class);
});

it('requires a score rather than storing a fake default', function () {
    $profile = CandidateProfile::factory()->create();
    $job = JobPost::factory()->create();

    expect(fn () => JobMatch::query()->create([
        'candidate_profile_id' => $profile->id, 'job_post_id' => $job->id,
    ]))->toThrow(QueryException::class);
});

it('rejects references to missing parents', function (string $field) {
    expect(fn () => JobMatch::factory()->create([$field => 999999]))->toThrow(QueryException::class);
})->with(['candidate_profile_id', 'job_post_id']);

it('deleting a candidate profile cascades only its matches and preserves jobs', function () {
    $profile = CandidateProfile::factory()->create();
    $matches = JobMatch::factory()->count(2)->for($profile)->create();
    $job = $matches->first()->jobPost;
    $other = JobMatch::factory()->for($job)->create();

    $profile->delete();

    foreach ($matches as $match) {
        $this->assertModelMissing($match);
        $this->assertModelExists($match->jobPost);
    }
    $this->assertModelExists($other);
});

it('deleting a job cascades only its matches and preserves candidate profiles', function () {
    $job = JobPost::factory()->create();
    $matches = JobMatch::factory()->count(2)->for($job)->create();
    $profile = $matches->first()->candidateProfile;
    $other = JobMatch::factory()->for($profile)->create();

    $job->delete();

    foreach ($matches as $match) {
        $this->assertModelMissing($match);
        $this->assertModelExists($match->candidateProfile);
    }
    $this->assertModelExists($other);
});
