<?php

use App\Models\IngestionRun;
use App\Models\JobSource;
use App\Models\RawJob;
use App\Models\User;
use App\Services\DiscoveredListing;
use App\Services\IngestionRunService;
use App\Services\JobDetailExtractor;
use App\Services\JobExtractionResult;
use App\Services\RawJobService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

it('represents optional discovery hints without requiring a persisted listing', function () {
    $this->freezeSecond();
    $source = JobSource::factory()->create();

    $listing = new DiscoveredListing($source->id, 'https://example.com/jobs/1');

    expect($listing->jobSourceId)->toBe($source->id);
    expect($listing->externalId)->toBeNull();
    expect($listing->detailUrl)->toBeNull();
    expect($listing->title)->toBeNull();
    expect($listing->metadata)->toBeNull();
    expect($listing->discoveredAt->equalTo(now()))->toBeTrue();
});

it('rejects invalid discovery urls', function (string $url) {
    expect(fn () => new DiscoveredListing(1, $url))->toThrow(ValidationException::class);
    expect(fn () => new DiscoveredListing(1, 'https://example.com', detailUrl: $url))->toThrow(ValidationException::class);
})->with(['not a url', 'file:///etc/passwd', 'javascript:alert(1)']);

it('persists sanitized raw content and exact provenance in a pending record', function () {
    $this->freezeSecond();
    $source = JobSource::factory()->create();
    $run = app(IngestionRunService::class)->startRun($source);
    $listing = new DiscoveredListing($source->id, 'https://user:password@example.com/jobs/1?api_key=SECRET&lang=en#section', '0',
        'https://example.com/jobs/1', 'مهندس برمجيات', 'Example Company', now(), ['page' => 1, 'headers' => ['Authorization' => 'SECRET']]);

    $raw = app(RawJobService::class)->store($run, $listing, [
        'title' => 'مهندس برمجيات', 'apiKey' => 'SECRET', 'nested' => ['cookies' => 'SECRET', 'description' => 'Public content'],
    ], 'Public job description');

    expect($raw)->toMatchArray([
        'job_source_id' => $source->id, 'ingestion_run_id' => $run->id, 'external_id' => '0', 'extraction_status' => 'pending',
        'source_url' => 'https://example.com/jobs/1?lang=en', 'discovered_title' => 'مهندس برمجيات',
        'raw_text' => 'Public job description', 'extracted_at' => null, 'discovery_metadata' => ['page' => 1],
        'raw_payload' => ['title' => 'مهندس برمجيات', 'nested' => ['description' => 'Public content']],
    ]);
    expect($raw->jobSource->is($source))->toBeTrue();
    expect($raw->ingestionRun->is($run))->toBeTrue();
    expect($source->rawJobs()->sole()->is($raw))->toBeTrue();
    expect($run->rawJobs()->sole()->is($raw))->toBeTrue();
    expect($raw->discovered_at->equalTo(now()))->toBeTrue();
    expect(json_encode($raw->toArray()))->not->toContain('SECRET');
    expect($run->fresh()->only(IngestionRun::COUNTERS))->toBe(array_fill_keys(IngestionRun::COUNTERS, 0));
});

it('uses external identity before urls with first-write-wins within one run', function () {
    $run = IngestionRun::factory()->create();
    $service = app(RawJobService::class);
    $first = $service->store($run, new DiscoveredListing($run->job_source_id, 'https://example.com/jobs/1', 'job-1'), ['title' => 'First']);

    $again = $service->store($run, new DiscoveredListing($run->job_source_id, 'https://example.com/new-url', 'job-1'), ['title' => 'Changed']);

    expect($again->id)->toBe($first->id);
    expect($again->raw_payload)->toBe(['title' => 'First']);
    $this->assertDatabaseCount('raw_jobs', 1);
});

it('normalizes url identity including scheme host default port query order and fragment', function () {
    $run = IngestionRun::factory()->create();
    $service = app(RawJobService::class);
    $first = $service->store($run, new DiscoveredListing($run->job_source_id, 'https://EXAMPLE.com:443/jobs/1?b=2&a=1#top'));

    $again = $service->store($run, new DiscoveredListing($run->job_source_id, 'https://example.com/jobs/1?a=1&b=2'));
    $detail = $service->store($run, new DiscoveredListing($run->job_source_id, 'https://example.com/list', detailUrl: 'https://example.com/jobs/1?b=2&a=1'));

    expect($again->id)->toBe($first->id);
    expect($detail->id)->toBe($first->id);
    $different = $service->store($run, new DiscoveredListing($run->job_source_id, 'https://example.com/jobs/1?a=2&b=2'));
    expect($different->id)->not->toBe($first->id);
});

it('permits the same identity in different runs and sources', function () {
    $source = JobSource::factory()->create();
    $runs = [IngestionRun::factory()->create(['job_source_id' => $source->id]), IngestionRun::factory()->create(['job_source_id' => $source->id]), IngestionRun::factory()->create()];

    foreach ($runs as $run) {
        app(RawJobService::class)->store($run, new DiscoveredListing($run->job_source_id, 'https://example.com/job', 'same-id'));
    }

    $this->assertDatabaseCount('raw_jobs', 3);
});

it('rejects cross-source provenance through the service and database', function () {
    $run = IngestionRun::factory()->create();
    $other = JobSource::factory()->create();

    expect(fn () => app(RawJobService::class)->store($run, new DiscoveredListing($other->id, 'https://example.com/job')))
        ->toThrow(ValidationException::class);
    expect(fn () => RawJob::factory()->create(['ingestion_run_id' => $run->id, 'job_source_id' => $other->id]))
        ->toThrow(QueryException::class);

    $this->assertDatabaseCount('raw_jobs', 0);
});

it('enforces the same-run identity constraint in the database', function () {
    $raw = RawJob::factory()->create();

    expect(fn () => RawJob::factory()->create(['ingestion_run_id' => $raw->ingestion_run_id, 'identity_key' => $raw->identity_key]))
        ->toThrow(QueryException::class);

    $this->assertDatabaseCount('raw_jobs', 1);
});

it('rejects new raw storage after run completion', function () {
    $run = IngestionRun::factory()->create();
    app(IngestionRunService::class)->markSucceeded($run);

    expect(fn () => app(RawJobService::class)->store($run, new DiscoveredListing($run->job_source_id, 'https://example.com/job')))
        ->toThrow(ValidationException::class);

    $this->assertDatabaseCount('raw_jobs', 0);
});

it('rejects credential-like free text before writing raw records', function (string $text) {
    $run = IngestionRun::factory()->create();

    expect(fn () => app(RawJobService::class)->store($run, new DiscoveredListing($run->job_source_id, 'https://example.com/job'), rawText: $text))
        ->toThrow(ValidationException::class);

    $this->assertDatabaseCount('raw_jobs', 0);
})->with(['Authorization: Bearer SECRET123456', 'api_key="SECRET"', 'Cookie: session=SECRET', 'password=SECRET', '-----BEGIN RSA PRIVATE KEY-----', 'token=SECRET', "GET /jobs HTTP/1.1\nX-Custom: value"]);

it('rejects non-json values in raw payloads', function () {
    $run = IngestionRun::factory()->create();

    expect(fn () => app(RawJobService::class)->store($run, new DiscoveredListing($run->job_source_id, 'https://example.com/job'), ['number' => INF]))
        ->toThrow(ValidationException::class);

    $this->assertDatabaseCount('raw_jobs', 0);
});

it('persists a structured extractor result without creating a job post', function () {
    $this->freezeSecond();
    $run = IngestionRun::factory()->create();
    $service = app(RawJobService::class);
    $raw = $service->store($run, new DiscoveredListing($run->job_source_id, 'https://example.com/job', title: 'Original title'), ['title' => 'Engineer']);
    $extractor = new class implements JobDetailExtractor
    {
        public function extract(RawJob $rawJob): JobExtractionResult
        {
            return new JobExtractionResult(['title' => $rawJob->raw_payload['title'], 'skills' => ['PHP'], 'responsibilities' => ['Build APIs'], 'external_url' => $rawJob->source_url]);
        }
    };

    $finished = $service->markExtracted($raw, $extractor->extract($raw));

    expect($finished->extraction_status)->toBe('extracted');
    expect($finished->extracted_data)->toBe(['title' => 'Engineer', 'external_url' => 'https://example.com/job', 'skills' => ['PHP'], 'responsibilities' => ['Build APIs']]);
    expect($finished->extracted_at->equalTo(now()))->toBeTrue();
    expect(fn () => $service->markExtracted($raw, new JobExtractionResult([])))->toThrow(ValidationException::class);
    expect(fn () => $service->markFailed($raw))->toThrow(ValidationException::class);
    $this->assertDatabaseCount('job_posts', 0);
});

it('isolates extraction failures with safe diagnostics and forbids re-finalization', function () {
    $a = RawJob::factory()->create();
    $b = RawJob::factory()->create();
    $original = $b->fresh()->getAttributes();
    $service = app(RawJobService::class);

    expect(fn () => $service->markFailed($a, 'secret exception trace'))->toThrow(ValidationException::class);
    $failed = $service->markFailed($a, 'invalid_payload');

    expect($failed->extraction_status)->toBe('failed');
    expect($failed->extraction_error_message)->toBe('The job payload could not be extracted.');
    expect($failed->extracted_at)->toBeNull();
    expect(fn () => $service->markExtracted($a, new JobExtractionResult([])))->toThrow(ValidationException::class);
    expect(fn () => $service->markFailed($a))->toThrow(ValidationException::class);
    expect($b->fresh()->getAttributes())->toBe($original);
    $this->assertDatabaseCount('job_posts', 0);
});

it('rolls back raw storage and finalization with their caller', function () {
    $run = IngestionRun::factory()->create();
    $service = app(RawJobService::class);
    $raw = RawJob::factory()->create();

    expect(fn () => DB::transaction(function () use ($run, $service, $raw): void {
        $service->store($run, new DiscoveredListing($run->job_source_id, 'https://example.com/new'));
        $service->markFailed($raw);
        throw new RuntimeException('rollback');
    }))->toThrow(RuntimeException::class);

    $this->assertDatabaseCount('raw_jobs', 1);
    expect($raw->fresh()->extraction_status)->toBe('pending');
});

it('allows only active administrators to read raw jobs', function (string $role, bool $active, int $status) {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
    $run = IngestionRun::factory()->create();
    $raw = app(RawJobService::class)->store($run, new DiscoveredListing($run->job_source_id, 'https://example.com/job'), ['title' => 'Engineer', 'api_key' => 'SECRET'], 'Description');
    $user = User::factory()->create(['role' => $role, 'is_active' => $active]);
    $this->withToken(JWTAuth::fromUser($user));

    $list = $this->getJson("/api/admin/job-sources/{$run->job_source_id}/raw-jobs")->assertStatus($status);
    $detail = $this->getJson("/api/admin/job-sources/{$run->job_source_id}/raw-jobs/{$raw->id}")->assertStatus($status);

    if ($status === 200) {
        $list->assertJsonMissingPath('data.0.raw_payload')->assertJsonMissingPath('data.0.raw_text')->assertJsonMissingPath('data.0.extracted_data')->assertJsonMissingPath('data.0.discovery_metadata');
        $detail->assertJsonPath('data.raw_payload', ['title' => 'Engineer'])->assertJsonPath('data.raw_text', 'Description');
        expect($detail->getContent())->not->toContain('SECRET');
    }
})->with([['admin', true, 200], ['super_admin', true, 200], ['candidate', true, 403], ['admin', false, 403], ['super_admin', false, 403]]);

it('scopes admin details and filtered pagination to the source', function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
    $raw = RawJob::factory()->create();
    $other = RawJob::factory()->create();
    RawJob::factory()->create(['ingestion_run_id' => $raw->ingestion_run_id]);
    app(RawJobService::class)->markFailed($raw);
    $this->withToken(JWTAuth::fromUser(User::factory()->create(['role' => 'admin'])));
    $base = "/api/admin/job-sources/{$raw->job_source_id}/raw-jobs";

    $this->getJson($base.'?per_page=1')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('meta.total', 2);
    $this->getJson($base."?ingestion_run_id={$raw->ingestion_run_id}&extraction_status=failed")
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $raw->id);
    $this->getJson($base."?ingestion_run_id={$other->ingestion_run_id}")->assertOk()->assertJsonCount(0, 'data');
    $this->getJson($base."/{$other->id}")->assertNotFound();
    $this->getJson($base.'?extraction_status=invalid&per_page=101')->assertUnprocessable()->assertJsonValidationErrors(['extraction_status', 'per_page']);
});

it('requires authentication on raw job reads', function () {
    $raw = RawJob::factory()->create();

    $this->getJson("/api/admin/job-sources/{$raw->job_source_id}/raw-jobs")->assertUnauthorized();
    $this->getJson("/api/admin/job-sources/{$raw->job_source_id}/raw-jobs/{$raw->id}")->assertUnauthorized();
});

it('localizes failure text while preserving source data and machine status', function (string $locale, string $message) {
    app()->setLocale($locale);
    $run = IngestionRun::factory()->create();
    $service = app(RawJobService::class);
    $raw = $service->store($run, new DiscoveredListing($run->job_source_id, 'https://example.com/job', title: 'Original external title'));

    $failed = $service->markFailed($raw);

    expect($failed->extraction_error_message)->toBe($message);
    expect($failed->extraction_status)->toBe('failed');
    expect($failed->extraction_error_code)->toBe('extraction_failed');
    expect($failed->discovered_title)->toBe('Original external title');
})->with([['en', 'Job detail extraction failed.'], ['ar', 'فشل استخراج تفاصيل الوظيفة.']]);
