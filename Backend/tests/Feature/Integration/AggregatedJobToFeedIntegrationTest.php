<?php

/**
 * Task16 final integration — Aggregated Job → Feed (PARTIAL).
 *
 * Traces the REAL ingestion chain through service boundaries:
 * source/run → discovery (RawJobService::store) → extraction
 * (markExtracted) → normalization (JobNormalizationService) →
 * deduplication (JobDeduplicationService::deduplicate) → searchable feed
 * (GET /api/jobs for admin-created canonical jobs).
 *
 * Classification: C (partially implemented).
 * - Enrichment does NOT exist anywhere in the codebase.
 * - No RawJob → JobPost publishing pipeline exists BY DESIGN
 *   (JobDeduplicationService docblock: "Phase 5 never creates JobPost
 *   records... so Phase 6 can review and create canonical vacancies safely").
 *   Distinct/possible outcomes leave canonical_job_post_id null.
 * This test pins the existing sub-chain AND the boundary: dedup never
 * auto-creates canonical feed entries. It must NOT be read as proof of a
 * complete aggregation pipeline.
 */

use App\Models\AuditLog;
use App\Models\JobPost;
use App\Models\JobSkill;
use App\Models\JobSource;
use App\Models\RawJob;
use App\Models\Skill;
use App\Models\User;
use App\Services\DiscoveredListing;
use App\Services\IngestionRunService;
use App\Services\JobDeduplicationService;
use App\Services\JobExtractionResult;
use App\Services\JobNormalizationService;
use App\Services\RawJobService;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

if (! function_exists('feedAs')) {
    function feedAs(object $test, User $user): object
    {
        auth()->forgetGuards();
        app(JWT::class)->unsetToken();

        return $test->withToken(JWTAuth::fromUser($user));
    }
}

if (! function_exists('feedNormalizedRawJob')) {
    function feedNormalizedRawJob(?JobSource $source = null, array $extracted = []): RawJob
    {
        $source ??= JobSource::factory()->create();
        $run = app(IngestionRunService::class)->startRun($source);
        $stored = app(RawJobService::class)->store(
            $run,
            new DiscoveredListing(
                $run->job_source_id,
                'https://example.com/job/'.fake()->uuid(),
                'ext-'.fake()->uuid(),
                null,
                'Backend Engineer',
                'Acme Corp',
            ),
            ['title' => 'Backend Engineer'],
        );
        $extracted = app(RawJobService::class)->markExtracted($stored, new JobExtractionResult([
            'title' => 'Backend Engineer',
            'company_name' => 'Acme Corp',
            'description' => 'Build and maintain backend services.',
            'country' => 'Egypt',
            'city' => 'Cairo',
            'skills' => ['Laravel', 'Docker'],
            ...$extracted,
        ]));

        return app(JobNormalizationService::class)->normalize($extracted);
    }
}

it('runs discovery extraction normalization and deduplication without fabricating canonical jobs', function () {
    $raw = feedNormalizedRawJob();

    expect($raw->extraction_status)->toBe(RawJob::STATUS_EXTRACTED)
        ->and($raw->normalization_status)->toBe(RawJob::NORMALIZATION_NORMALIZED)
        ->and($raw->normalized_data['title']['normalized'])->toBe('Backend Engineer')
        ->and($raw->normalized_data['company_name']['normalized'])->toBe('Acme Corp');

    $decided = app(JobDeduplicationService::class)->deduplicate($raw);

    // Fresh listing with no canonical evidence stays non-canonical:
    // the pipeline deliberately stops before JobPost creation.
    expect($decided->deduplication_status)->toBe(RawJob::DEDUPLICATION_DISTINCT)
        ->and($decided->canonical_job_post_id)->toBeNull();

    // Re-running dedup is a no-op on the finalized record.
    $again = app(JobDeduplicationService::class)->deduplicate($decided->fresh());
    expect($again->deduplication_status)->toBe(RawJob::DEDUPLICATION_DISTINCT);

    // Pipeline audits exist as observability evidence.
    expect(AuditLog::query()->where('entity_type', 'raw_job')->where('entity_id', $raw->id)->count())->toBeGreaterThan(0);

    // The undecided raw job never leaks into the candidate feed.
    $candidate = User::factory()->create(['role' => 'candidate']);
    feedAs($this, $candidate)->getJson('/api/jobs')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('deduplicates repeated discoveries and surfaces canonical jobs in the searchable feed', function () {
    $source = JobSource::factory()->create();
    $run = app(IngestionRunService::class)->startRun($source);
    $listing = new DiscoveredListing($source->id, 'https://example.com/jobs/backend-1', 'job-1', null, 'Backend Engineer', 'Acme Corp');

    $first = app(RawJobService::class)->store($run, $listing, ['title' => 'Backend Engineer']);
    $repeat = app(RawJobService::class)->store($run, $listing, ['title' => 'Changed title']);

    // Same-run identity is first-write-wins: no duplicate raw records.
    expect($repeat->id)->toBe($first->id);
    $this->assertDatabaseCount('raw_jobs', 1);

    // Canonical jobs (admin/direct path — the only production JobPost
    // producer) are the same entity consumed by the searchable feed.
    $laravel = Skill::factory()->create(['name' => 'Laravel']);
    $job = JobPost::factory()->create([
        'title' => 'Senior Backend Engineer',
        'canonical_role' => 'Backend Engineer',
        'is_active' => true,
    ]);
    JobSkill::factory()->for($job)->for($laravel)->create(['is_required' => true]);

    $candidate = User::factory()->create(['role' => 'candidate']);

    feedAs($this, $candidate)->getJson('/api/jobs')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $job->id)
        ->assertJsonPath('data.0.required_skills.0.name', 'Laravel');

    // Feed search/filter behavior over title, canonical role and skills.
    feedAs($this, $candidate)->getJson('/api/jobs?search=laravel')
        ->assertOk()
        ->assertJsonCount(1, 'data');

    feedAs($this, $candidate)->getJson('/api/jobs?search=unrelated-stack')
        ->assertOk()
        ->assertJsonCount(0, 'data');

    feedAs($this, $candidate)->getJson("/api/jobs?required_skill_ids[]={$laravel->id}")
        ->assertOk()
        ->assertJsonCount(1, 'data');
});
