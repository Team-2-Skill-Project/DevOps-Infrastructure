<?php

use App\Models\CandidateSkill;
use App\Models\JobPost;
use App\Models\JobSource;
use App\Models\RawJob;
use App\Models\Skill;
use App\Models\SkillAlias;
use App\Services\DiscoveredListing;
use App\Services\IngestionRunService;
use App\Services\JobExtractionResult;
use App\Services\JobNormalizationService;
use App\Services\RawJobService;
use Illuminate\Validation\ValidationException;

function phaseFourRawJob(array $data = []): RawJob
{
    $rawJob = app(RawJobService::class)->store(
        app(IngestionRunService::class)->startRun($source = JobSource::factory()->create()),
        new DiscoveredListing($source->id, 'https://example.com/job/'.fake()->uuid()),
    );

    return app(RawJobService::class)->markExtracted($rawJob, new JobExtractionResult($data));
}

it('normalizes extracted data without changing evidence or creating domain jobs', function () {
    $original = [
        'title' => '  Senior   BACKEND Developer ',
        'company_name' => '  Example   Company ',
        'description' => '  Build reliable APIs.  ',
        'employment_type' => 'Full-time',
        'job_type' => 'Job',
        'work_mode' => 'Remote / Home',
        'experience_level' => 'Senior',
        'min_years_experience' => 3,
        'max_years_experience' => 7,
        'country' => '  Egypt ',
        'city' => '  Cairo  ',
        'application_url' => 'https://EXAMPLE.com/apply?id=2#top',
        'skills' => [' PHP ', 'Unknown Tool'],
        'responsibilities' => ['  Build APIs  '],
    ];
    $skill = Skill::factory()->create(['name' => 'PHP', 'normalized_name' => 'php']);
    SkillAlias::factory()->for($skill)->create(['alias' => 'PHP 8']);
    $candidateSkill = CandidateSkill::factory()->create(['skill_id' => $skill->id]);
    $candidateSnapshot = $candidateSkill->fresh()->getAttributes();
    $rawJob = phaseFourRawJob(array_replace($original, ['skills' => ['PHP 8', 'Unknown Tool']]));
    $evidence = $rawJob->extracted_data;

    $normalized = app(JobNormalizationService::class)->normalize($rawJob);

    expect($normalized->normalization_status)->toBe(RawJob::NORMALIZATION_NORMALIZED)
        ->and($normalized->normalized_at)->not->toBeNull()
        ->and($normalized->extracted_data)->toBe($evidence)
        ->and($normalized->normalized_data['title'])->toBe(['original' => $original['title'], 'normalized' => 'Senior BACKEND Developer'])
        ->and($normalized->normalized_data['work_mode']['normalized'])->toBe('remote')
        ->and($normalized->normalized_data['employment_type']['normalized'])->toBe('full_time')
        ->and($normalized->normalized_data['min_years_experience']['normalized'])->toBe(3)
        ->and($normalized->normalized_data['application_url']['normalized'])->toBe('https://example.com/apply?id=2')
        ->and($normalized->normalized_data['skills'][0])->toMatchArray(['original' => 'PHP 8', 'skill_id' => $skill->id, 'canonical_name' => 'PHP'])
        ->and($normalized->normalized_data['skills'][1])->toMatchArray(['original' => 'Unknown Tool', 'skill_id' => null, 'canonical_name' => null])
        ->and($candidateSkill->fresh()->getAttributes())->toBe($candidateSnapshot)
        ->and(JobPost::query()->count())->toBe(0);
});

it('rejects normalization before successful extraction and preserves machine statuses', function () {
    $rawJob = RawJob::factory()->create();

    expect(fn () => app(JobNormalizationService::class)->normalize($rawJob))
        ->toThrow(ValidationException::class);

    $rawJob->update(['extraction_status' => RawJob::STATUS_FAILED]);

    expect(fn () => app(JobNormalizationService::class)->normalize($rawJob))
        ->toThrow(ValidationException::class);
});

it('fails safely for invalid experience and forbids finalization twice', function () {
    $rawJob = phaseFourRawJob(['min_years_experience' => -1]);

    $failed = app(JobNormalizationService::class)->normalize($rawJob);

    expect($failed->normalization_status)->toBe(RawJob::NORMALIZATION_FAILED)
        ->and($failed->normalization_error_code)->toBe('invalid_data')
        ->and($failed->normalization_error_message)->toBe('The extracted job data could not be normalized.')
        ->and(fn () => app(JobNormalizationService::class)->normalize($failed))
        ->toThrow(ValidationException::class);
});

it('leaves unknown mappings null while preserving original values', function () {
    $rawJob = phaseFourRawJob([
        'work_mode' => 'Flexible anywhere',
        'employment_type' => 'Something unusual',
        'job_type' => 'Volunteer role',
        'country' => '  Neverland  ',
        'skills' => [],
    ]);

    $normalized = app(JobNormalizationService::class)->normalize($rawJob);

    expect($normalized->normalized_data['work_mode'])->toBe(['original' => 'Flexible anywhere', 'normalized' => null])
        ->and($normalized->normalized_data['employment_type']['normalized'])->toBeNull()
        ->and($normalized->normalized_data['job_type']['normalized'])->toBeNull()
        ->and($normalized->normalized_data['country']['normalized'])->toBe('Neverland');
});

it('localizes normalization messages without translating machine identifiers', function (string $locale, string $message) {
    app()->setLocale($locale);
    $rawJob = phaseFourRawJob(['min_years_experience' => -1]);

    $normalized = app(JobNormalizationService::class)->normalize($rawJob);

    expect($normalized->normalization_status)->toBe('failed')
        ->and($normalized->normalization_error_message)->toBe($message);
})->with([
    ['en', 'The extracted job data could not be normalized.'],
    ['ar', 'تعذر تطبيع بيانات الوظيفة المستخرجة.'],
]);
