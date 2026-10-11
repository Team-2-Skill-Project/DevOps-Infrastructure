<?php

use App\Models\AuditLog;
use App\Models\IngestionRun;
use App\Models\JobPost;
use App\Models\JobSource;
use App\Models\JobSourceReference;
use App\Models\RawJob;
use App\Services\DiscoveredListing;
use App\Services\IngestionRunService;
use App\Services\JobDeduplicationService;
use App\Services\JobExtractionResult;
use App\Services\JobNormalizationService;
use App\Services\RawJobService;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

function importNormalizedRawJob(?JobSource $source = null, ?IngestionRun $run = null, array $extracted = []): RawJob
{
    $source ??= JobSource::factory()->create();
    $run ??= app(IngestionRunService::class)->startRun($source);
    $stored = app(RawJobService::class)->store(
        $run,
        new DiscoveredListing($run->job_source_id, 'https://example.com/job/'.fake()->uuid()),
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

it('audits ingestion run start and finish with counters', function () {
    $source = JobSource::factory()->create();
    $run = app(IngestionRunService::class)->startRun($source, 'manual');

    $started = AuditLog::query()->where('action', 'ingestion_run_started')->sole();
    expect($started->entity_type->value)->toBe('ingestion_run')
        ->and($started->entity_id)->toBe($run->id)
        ->and($started->source->value)->toBe('ingestion')
        ->and($started->actor_id)->toBeNull()
        ->and($started->after)->toBe(['status' => 'running'])
        ->and($started->metadata['job_source_id'])->toBe($source->id)
        ->and($started->metadata['trigger_type'])->toBe('manual');

    app(IngestionRunService::class)->markSucceeded($run, [
        'discovered_count' => 5, 'fetched_count' => 5, 'created_count' => 4,
        'updated_count' => 0, 'skipped_count' => 1, 'failed_count' => 0,
    ]);

    $finished = AuditLog::query()->where('action', 'ingestion_run_finished')->sole();
    expect($finished->entity_id)->toBe($run->id)
        ->and($finished->before)->toBe(['status' => 'running'])
        ->and($finished->after)->toBe(['status' => 'succeeded'])
        ->and($finished->metadata['discovered_count'])->toBe(5)
        ->and($finished->metadata['skipped_count'])->toBe(1);
});

it('audits run failures with error codes without losing the record', function () {
    $source = JobSource::factory()->create();
    $run = app(IngestionRunService::class)->startRun($source);

    app(IngestionRunService::class)->markFailed($run, [], 'source_unavailable', ['http_status' => 503]);

    $finished = AuditLog::query()->where('action', 'ingestion_run_finished')->sole();
    expect($finished->after)->toBe(['status' => 'failed'])
        ->and($finished->metadata['error_code'])->toBe('source_unavailable');
});

it('audits extraction failures with run and source references', function () {
    $source = JobSource::factory()->create();
    $run = app(IngestionRunService::class)->startRun($source);
    $raw = app(RawJobService::class)->store($run, new DiscoveredListing($source->id, 'https://example.com/job/1'));

    app(RawJobService::class)->markFailed($raw, 'unsupported_format');

    $log = AuditLog::query()->where('action', 'raw_job_extraction_failed')->sole();
    expect($log->entity_type->value)->toBe('raw_job')
        ->and($log->entity_id)->toBe($raw->id)
        ->and($log->before)->toBe(['extraction_status' => 'pending'])
        ->and($log->after)->toBe(['extraction_status' => 'failed'])
        ->and($log->metadata['error_code'])->toBe('unsupported_format')
        ->and($log->metadata['ingestion_run_id'])->toBe($run->id)
        ->and($log->metadata['job_source_id'])->toBe($source->id);
    expect(json_encode($log->toArray()))->not->toContain('Public job description');
});

it('does not audit successful extractions per row to bound volume', function () {
    $source = JobSource::factory()->create();
    $run = app(IngestionRunService::class)->startRun($source);
    $raw = app(RawJobService::class)->store($run, new DiscoveredListing($source->id, 'https://example.com/job/1'));

    app(RawJobService::class)->markExtracted($raw, new JobExtractionResult(['title' => 'Backend Engineer']));

    expect(AuditLog::query()->where('action', 'raw_job_extraction_failed')->count())->toBe(0);
});

it('audits normalization success and failure without duplicating payloads', function () {
    $raw = importNormalizedRawJob();

    $normalized = AuditLog::query()->where('action', 'job_normalized')->sole();
    expect($normalized->entity_id)->toBe($raw->id)
        ->and($normalized->before)->toBe(['normalization_status' => 'pending'])
        ->and($normalized->after)->toBe(['normalization_status' => 'normalized'])
        ->and($normalized->metadata['job_source_id'])->toBe($raw->job_source_id)
        ->and($normalized->metadata)->toHaveKey('matched_skill_count');
    expect($normalized->after)->not->toHaveKey('normalized_data');

    $bad = importNormalizedRawJob(extracted: ['min_years_experience' => 5, 'max_years_experience' => 2]);
    $failed = AuditLog::query()->where('action', 'job_normalization_failed')->sole();
    expect($failed->entity_id)->toBe($bad->id)
        ->and($failed->after)->toBe(['normalization_status' => 'failed'])
        ->and($failed->metadata['error_code'])->toBe('invalid_data');
});

it('audits deduplication decisions with canonical references', function () {
    $raw = importNormalizedRawJob();

    app(JobDeduplicationService::class)->deduplicate($raw);

    $distinct = AuditLog::query()->where('action', 'job_deduplicated')->sole();
    expect($distinct->entity_id)->toBe($raw->id)
        ->and($distinct->before)->toBe(['deduplication_status' => 'pending'])
        ->and($distinct->after['deduplication_status'])->toBe('distinct')
        ->and($distinct->after['canonical_job_post_id'])->toBeNull()
        ->and($distinct->metadata)->toHaveKey('method')
        ->and($distinct->metadata['fingerprint_version'])->toBe(1);
    expect($distinct->after)->not->toHaveKey('deduplication_evidence');

    $job = JobPost::factory()->create(['title' => 'Backend Engineer']);
    $second = importNormalizedRawJob();
    JobSourceReference::query()->create([
        'job_post_id' => $job->id,
        'raw_job_id' => $second->id,
        'job_source_id' => $second->job_source_id,
        'ingestion_run_id' => $second->ingestion_run_id,
        'external_id' => $second->external_id,
        'source_url' => $second->source_url,
        'detail_url' => $second->detail_url,
        'match_method' => 'same_source_external_id',
    ]);
    app(JobDeduplicationService::class)->deduplicate($second);

    $matched = AuditLog::query()->where('action', 'job_deduplicated')->latest('id')->firstOrFail();
    expect($matched->entity_id)->toBe($second->id)
        ->and($matched->after['deduplication_status'])->toBe('matched')
        ->and($matched->after['canonical_job_post_id'])->toBe($job->id);
    expect(AuditLog::query()->where('action', 'job_deduplicated')->count())->toBe(2);
});

it('does not duplicate deduplication audits on idempotent re-runs', function () {
    $raw = importNormalizedRawJob();

    app(JobDeduplicationService::class)->deduplicate($raw);
    app(JobDeduplicationService::class)->deduplicate($raw);

    expect(AuditLog::query()->where('action', 'job_deduplicated')->count())->toBe(1);
});
