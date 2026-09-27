<?php

use App\Models\CandidateSkill;
use App\Models\Company;
use App\Models\CompanyAlias;
use App\Models\IngestionRun;
use App\Models\JobPost;
use App\Models\JobSource;
use App\Models\JobSourceReference;
use App\Models\RawJob;
use App\Models\Skill;
use App\Models\User;
use App\Services\DiscoveredListing;
use App\Services\DuplicateDecision;
use App\Services\IngestionRunService;
use App\Services\JobDeduplicationService;
use App\Services\JobExtractionResult;
use App\Services\JobFingerprintService;
use App\Services\JobManagementService;
use App\Services\JobNormalizationService;
use App\Services\RawJobService;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

function phaseFiveNormalizedRawJob(
    ?JobSource $source = null,
    ?IngestionRun $run = null,
    array $listing = [],
    array $extracted = [],
): RawJob {
    $source ??= JobSource::factory()->create();
    $run ??= app(IngestionRunService::class)->startRun($source);
    $stored = app(RawJobService::class)->store(
        $run,
        new DiscoveredListing(
            $run->job_source_id,
            $listing['sourceUrl'] ?? 'https://example.com/job/'.fake()->uuid(),
            $listing['externalId'] ?? null,
            $listing['detailUrl'] ?? null,
            $listing['title'] ?? null,
            $listing['companyName'] ?? null,
            metadata: $listing['metadata'] ?? null,
        ),
        $listing['rawPayload'] ?? null,
    );
    $extracted = app(RawJobService::class)->markExtracted($stored, new JobExtractionResult([
        'title' => 'Backend Engineer',
        'company_name' => 'Acme Corp',
        'country' => 'Egypt',
        'city' => 'Cairo',
        ...$extracted,
    ]));

    return app(JobNormalizationService::class)->normalize($extracted);
}

function phaseFiveReference(JobPost $jobPost, RawJob $rawJob, ?string $method = null): JobSourceReference
{
    return JobSourceReference::query()->create([
        'job_post_id' => $jobPost->id,
        'raw_job_id' => $rawJob->id,
        'job_source_id' => $rawJob->job_source_id,
        'ingestion_run_id' => $rawJob->ingestion_run_id,
        'external_id' => $rawJob->external_id,
        'source_url' => $rawJob->source_url,
        'detail_url' => $rawJob->detail_url,
        'match_method' => $method ?? 'same_source_external_id',
    ]);
}

it('rejects deduplication before successful normalization', function () {
    $pending = RawJob::factory()->create();
    $failed = phaseFiveNormalizedRawJob();
    $failed->update(['normalization_status' => RawJob::NORMALIZATION_FAILED]);

    foreach ([$pending, $failed] as $rawJob) {
        expect(fn () => app(JobDeduplicationService::class)->deduplicate($rawJob))
            ->toThrow(ValidationException::class);
        expect(fn () => app(JobDeduplicationService::class)->evaluate($rawJob))
            ->toThrow(ValidationException::class);
    }

    expect(RawJob::query()->where('deduplication_status', '!=', 'pending')->count())->toBe(0);
});

it('matches same source and external id while ignoring cross source collisions', function () {
    $source = JobSource::factory()->create();
    $jobPost = JobPost::factory()->create();
    $first = phaseFiveNormalizedRawJob($source, null, ['externalId' => 'ext-123']);
    phaseFiveReference($jobPost, $first);
    $second = phaseFiveNormalizedRawJob($source, null, ['externalId' => 'ext-123']);

    $decision = app(JobDeduplicationService::class)->deduplicate($second);

    expect($decision->deduplication_status)->toBe('matched')
        ->and($decision->deduplication_method)->toBe('same_source_external_id')
        ->and((int) $decision->canonical_job_post_id)->toBe($jobPost->id)
        ->and($jobPost->sourceReferences()->count())->toBe(2);

    $foreign = phaseFiveNormalizedRawJob(null, null, ['externalId' => 'ext-123']);

    $foreignDecision = app(JobDeduplicationService::class)->deduplicate($foreign);

    expect($foreignDecision->deduplication_status)->not->toBe('matched')
        ->and($foreignDecision->canonical_job_post_id)->toBeNull();
});

it('reuses an existing reference idempotently without duplicating provenance', function () {
    $jobPost = JobPost::factory()->create();
    $rawJob = phaseFiveNormalizedRawJob();
    phaseFiveReference($jobPost, $rawJob, 'existing_reference');

    $first = app(JobDeduplicationService::class)->deduplicate($rawJob);
    $second = app(JobDeduplicationService::class)->deduplicate($rawJob);

    expect($first->deduplication_status)->toBe('matched')
        ->and($first->deduplication_method)->toBe('existing_reference')
        ->and((int) $first->canonical_job_post_id)->toBe($jobPost->id)
        ->and(JobSourceReference::query()->where('raw_job_id', $rawJob->id)->count())->toBe(1)
        ->and($second->deduplication_status)->toBe('matched')
        ->and((int) $second->canonical_job_post_id)->toBe($jobPost->id)
        ->and(JobSourceReference::query()->count())->toBe(1);
});

it('matches exact stable normalized urls regardless of query order', function () {
    $jobPost = JobPost::factory()->create();
    $first = phaseFiveNormalizedRawJob(
        listing: ['detailUrl' => 'https://example.com/jobs/abc?b=2&a=1'],
        extracted: ['title' => 'Platform Engineer', 'company_name' => 'Globex'],
    );
    phaseFiveReference($jobPost, $first, 'exact_normalized_url');
    $second = phaseFiveNormalizedRawJob(
        listing: ['detailUrl' => 'https://example.com/jobs/abc?a=1&b=2'],
        extracted: ['title' => 'Unrelated Title Here', 'company_name' => 'Initech', 'city' => 'Giza'],
    );

    $decision = app(JobDeduplicationService::class)->deduplicate($second);

    expect($decision->deduplication_status)->toBe('matched')
        ->and($decision->deduplication_method)->toBe('exact_normalized_url')
        ->and((int) $decision->canonical_job_post_id)->toBe($jobPost->id);
});

it('computes a stable versioned fingerprint immune to ordering and volatile metadata', function () {
    $service = app(JobFingerprintService::class);
    $base = ['title' => 'Backend Engineer', 'company_name' => 'Acme Corp', 'country' => 'Egypt', 'city' => 'Cairo'];

    $first = phaseFiveNormalizedRawJob(listing: ['metadata' => ['page' => 1]], extracted: $base);
    $reordered = phaseFiveNormalizedRawJob(
        listing: ['metadata' => ['page' => 9]],
        extracted: ['city' => 'Cairo', 'country' => 'Egypt', 'company_name' => 'Acme Corp', 'title' => 'Backend Engineer', 'skills' => ['Laravel', 'PHP']],
    );

    $firstResult = app(JobDeduplicationService::class)->deduplicate($first);
    $secondResult = app(JobDeduplicationService::class)->deduplicate($reordered);

    expect($firstResult->fingerprint)->toBe($secondResult->fingerprint)
        ->and($firstResult->fingerprint)->toMatch('/^[0-9a-f]{64}$/')
        ->and($firstResult->fingerprint_version)->toBe(JobFingerprintService::VERSION)
        ->and(JobFingerprintService::VERSION)->toBe(1)
        ->and($service->fingerprintForNormalized(
            $firstResult->normalized_data,
            ...app(JobDeduplicationService::class)->resolveCompany('Acme Corp'),
        ))->toBe($firstResult->fingerprint);
});

it('changes fingerprints for meaningful vacancy differences', function () {
    $titleChanged = phaseFiveNormalizedRawJob(extracted: ['title' => 'Senior Backend Engineer']);
    $locationChanged = phaseFiveNormalizedRawJob(extracted: ['city' => 'Alexandria']);
    $companyChanged = phaseFiveNormalizedRawJob(extracted: ['company_name' => 'Other Corp']);
    $base = app(JobFingerprintService::class)->fingerprintForNormalized(
        phaseFiveNormalizedRawJob()->normalized_data,
    );

    expect($titleChanged->fresh()->fingerprint ?? app(JobDeduplicationService::class)->deduplicate($titleChanged)->fingerprint)
        ->not->toBe($base);
    expect(app(JobDeduplicationService::class)->deduplicate($locationChanged)->fingerprint)->not->toBe($base);
    expect(app(JobDeduplicationService::class)->deduplicate($companyChanged)->fingerprint)->not->toBe($base);
});

it('never merges on fingerprint alone', function () {
    $first = phaseFiveNormalizedRawJob();
    app(JobDeduplicationService::class)->deduplicate($first);
    $second = phaseFiveNormalizedRawJob();

    $decision = app(JobDeduplicationService::class)->deduplicate($second);

    expect($decision->fingerprint)->toBe($first->fresh()->fingerprint)
        ->and($decision->deduplication_status)->not->toBe('matched')
        ->and($decision->canonical_job_post_id)->toBeNull()
        ->and(JobSourceReference::query()->where('raw_job_id', $second->id)->count())->toBe(0)
        ->and(JobPost::query()->count())->toBe(0);
});

it('resolves companies only through exact canonical or alias matches', function () {
    $company = Company::factory()->create(['name' => 'Acme Corporation', 'normalized_name' => 'acme corporation']);
    CompanyAlias::factory()->for($company)->create(['alias' => 'Acme Inc', 'normalized_alias' => 'acme inc']);
    $service = app(JobDeduplicationService::class);

    expect($service->resolveCompany('ACME   Corporation'))->toBe([$company->id, 'canonical']);
    expect($service->resolveCompany('acme inc'))->toBe([$company->id, 'alias']);
    expect($service->resolveCompany('Brand New Startup XYZ'))->toBe([null, 'text']);
    expect($service->resolveCompany(null))->toBe([null, 'unknown']);
    expect(Company::query()->count())->toBe(1);
});

it('keeps meaningful seniority and specialization title differences apart', function (string $first, string $second) {
    $service = app(JobFingerprintService::class);

    expect($service->comparisonTitle($first))->not->toBe($service->comparisonTitle($second))
        ->and($service->titleSimilarity($first, $second))->toBeLessThan(1.0);
})->with([
    ['Backend Engineer', 'Senior Backend Engineer'],
    ['Backend Engineer', 'Frontend Engineer'],
    ['Software Engineer', 'Staff Software Engineer'],
]);

it('does not merge same company and similar titles without strong evidence', function () {
    $first = phaseFiveNormalizedRawJob(extracted: ['title' => 'Backend Engineer']);
    app(JobDeduplicationService::class)->deduplicate($first);
    $second = phaseFiveNormalizedRawJob(extracted: ['title' => 'Backend Engineer']);

    $decision = app(JobDeduplicationService::class)->deduplicate($second);

    expect($decision->deduplication_status)->not->toBe('matched')
        ->and($decision->canonical_job_post_id)->toBeNull()
        ->and(JobPost::query()->count())->toBe(0);
});

it('marks uncertain similarity as possible without merging', function () {
    $source = JobSource::factory()->create();
    $first = phaseFiveNormalizedRawJob($source, null, [], ['title' => 'Backend Engineer']);
    app(JobDeduplicationService::class)->deduplicate($first);
    $second = phaseFiveNormalizedRawJob($source, null, [], ['title' => 'Backend Engineer API']);

    $decision = app(JobDeduplicationService::class)->deduplicate($second);

    expect($decision->deduplication_status)->toBe('possible')
        ->and($decision->canonical_job_post_id)->toBeNull()
        ->and($decision->deduplication_method)->toBeIn(['fingerprint_candidate', 'title_company_similarity'])
        ->and(JobSourceReference::query()->where('raw_job_id', $second->id)->count())->toBe(0)
        ->and(JobPost::query()->count())->toBe(0);
});

it('marks clearly unrelated vacancies as distinct', function () {
    $rawJob = phaseFiveNormalizedRawJob(extracted: [
        'title' => 'Pediatric Nurse Night Shift',
        'company_name' => 'City Hospital',
        'country' => 'Egypt',
        'city' => 'Aswan',
    ]);

    $decision = app(JobDeduplicationService::class)->deduplicate($rawJob);

    expect($decision->deduplication_status)->toBe('distinct')
        ->and($decision->deduplication_method)->toBe('no_match')
        ->and($decision->canonical_job_post_id)->toBeNull();
});

it('persists sanitized explainable evidence without touching prior snapshots', function () {
    $skill = Skill::factory()->create(['name' => 'PHP', 'normalized_name' => 'php']);
    $candidateSkill = CandidateSkill::factory()->create(['skill_id' => $skill->id]);
    $candidateSnapshot = $candidateSkill->fresh()->getAttributes();
    $skillCount = Skill::query()->count();
    $jobPost = JobPost::factory()->create();
    $rawJob = phaseFiveNormalizedRawJob();
    $extractedSnapshot = $rawJob->extracted_data;
    $normalizedSnapshot = $rawJob->normalized_data;
    phaseFiveReference($jobPost, $rawJob, 'existing_reference');

    $decision = app(JobDeduplicationService::class)->deduplicate($rawJob);
    $evidence = $decision->deduplication_evidence ?? [];

    expect($decision->deduplication_status)->toBe('matched')
        ->and($decision->extracted_data)->toBe($extractedSnapshot)
        ->and($decision->normalized_data)->toBe($normalizedSnapshot)
        ->and(array_keys($evidence))->each->toBeIn([
            'fingerprint', 'fingerprint_version', 'company_match', 'company_id', 'comparison_title',
            'comparison_company', 'location', 'existing_reference', 'existing_job_post_id',
            'same_source_external_id', 'exact_normalized_url', 'fingerprint_candidate',
            'fingerprint_candidate_count', 'title_similarity', 'location_agreement',
            'date_agreement', 'candidate_raw_job_id',
        ])
        ->and(json_encode($evidence))->not->toContain('SECRET')
        ->and(json_encode($evidence))->not->toContain('password')
        ->and(json_encode($evidence))->not->toContain('token=')
        ->and(json_encode($evidence))->not->toContain('Authorization')
        ->and($candidateSkill->fresh()->getAttributes())->toBe($candidateSnapshot)
        ->and(Skill::query()->count())->toBe($skillCount)
        ->and($decision->deduplicated_at)->not->toBeNull();
});

it('preserves additive provenance across sources and runs', function () {
    $jobPost = JobPost::factory()->create();
    $first = phaseFiveNormalizedRawJob(listing: ['externalId' => 'a-1']);
    $second = phaseFiveNormalizedRawJob(listing: ['externalId' => 'b-1']);
    phaseFiveReference($jobPost, $first);
    phaseFiveReference($jobPost, $second);
    app(JobDeduplicationService::class)->deduplicate($first);

    $references = $jobPost->sourceReferences()->orderBy('id')->get();

    expect($references)->toHaveCount(2)
        ->and($references[0]->job_source_id)->toBe($first->job_source_id)
        ->and($references[0]->ingestion_run_id)->toBe($first->ingestion_run_id)
        ->and($references[1]->job_source_id)->toBe($second->job_source_id)
        ->and($references[1]->ingestion_run_id)->toBe($second->ingestion_run_id)
        ->and($first->fresh()->sourceReference->job_post_id)->toBe($jobPost->id)
        ->and($first->fresh()->canonicalJobPost->id)->toBe($jobPost->id);
});

it('rejects provenance mismatches at the database boundary', function () {
    $rawJob = phaseFiveNormalizedRawJob();
    $otherSource = JobSource::factory()->create();

    expect(fn () => JobSourceReference::query()->create([
        'job_post_id' => JobPost::factory()->create()->id,
        'raw_job_id' => $rawJob->id,
        'job_source_id' => $otherSource->id,
        'ingestion_run_id' => $rawJob->ingestion_run_id,
        'source_url' => $rawJob->source_url,
        'match_method' => 'existing_reference',
    ]))->toThrow(QueryException::class);
});

it('keeps legacy direct job posts intact without requiring references', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $company = Company::factory()->create();
    $direct = app(JobManagementService::class)->save(null, [
        'company_id' => $company->id,
        'title' => 'Direct Hire Role',
        'description' => 'Created by an administrator.',
    ], $admin);

    expect($direct->source)->toBe('direct')
        ->and($direct->sourceReferences()->count())->toBe(0)
        ->and($direct->external_id)->toBeNull();

    $rawJob = phaseFiveNormalizedRawJob();

    app(JobDeduplicationService::class)->deduplicate($rawJob);

    expect($direct->fresh()->title)->toBe('Direct Hire Role')
        ->and(RawJob::query()->whereNotNull('external_id')->count())->toBe(0);
});

it('exposes deduplication state to administrators only', function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
    $rawJob = phaseFiveNormalizedRawJob();
    app(JobDeduplicationService::class)->deduplicate($rawJob);
    $this->withToken(JWTAuth::fromUser(User::factory()->create(['role' => 'admin'])));
    $base = "/api/admin/job-sources/{$rawJob->job_source_id}/raw-jobs";

    $this->getJson($base)->assertOk()->assertJsonPath('data.0.deduplication_status', 'distinct')
        ->assertJsonMissingPath('data.0.deduplication_evidence');
    $this->getJson("{$base}/{$rawJob->id}")->assertOk()
        ->assertJsonPath('data.deduplication_status', 'distinct')
        ->assertJsonPath('data.fingerprint_version', JobFingerprintService::VERSION)
        ->assertJsonPath('data.fingerprint', $rawJob->fresh()->fingerprint)
        ->assertJsonStructure(['data' => ['deduplication_evidence', 'deduplication_method', 'canonical_job_post_id']]);

    $this->getJson("{$base}?deduplication_status=distinct")->assertOk()->assertJsonCount(1, 'data');
    $this->getJson("{$base}?deduplication_status=invalid")->assertUnprocessable();
});

it('localizes deduplication guard messages without translating machine identifiers', function (string $locale, string $message) {
    app()->setLocale($locale);
    $rawJob = RawJob::factory()->create();

    try {
        app(JobDeduplicationService::class)->deduplicate($rawJob);
        $this->fail('Deduplication should require normalization.');
    } catch (ValidationException $exception) {
        expect($exception->errors()['normalization_status'][0])->toBe($message);
    }

    expect($rawJob->fresh()->deduplication_status)->toBe('pending');
})->with([
    ['en', 'Job deduplication requires successful normalization.'],
    ['ar', 'يتطلب إزالة تكرار الوظيفة نجاح التطبيع أولًا.'],
]);

it('returns a structured duplicate decision value object', function () {
    $rawJob = phaseFiveNormalizedRawJob();

    $decision = app(JobDeduplicationService::class)->evaluate($rawJob);

    expect($decision)->toBeInstanceOf(DuplicateDecision::class)
        ->and($decision->decision)->toBeIn(['matched', 'possible', 'distinct', 'failed'])
        ->and($decision->fingerprint)->toMatch('/^[0-9a-f]{64}$/')
        ->and($decision->fingerprintVersion)->toBe(1);
});
