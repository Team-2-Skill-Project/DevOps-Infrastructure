<?php

use App\Models\IngestionRun;
use App\Models\JobSource;
use App\Models\User;
use App\Services\IngestionRunService;
use App\Services\JobSourceHealthService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

it('starts a source-owned running record with zero counters', function (string $trigger) {
    $this->freezeSecond();
    $source = JobSource::factory()->create();

    $run = app(IngestionRunService::class)->startRun($source, $trigger);

    expect($run->status)->toBe('running');
    expect($run->trigger_type)->toBe($trigger);
    expect($run->started_at->equalTo(now()))->toBeTrue();
    expect($run->finished_at)->toBeNull();
    expect($run->jobSource->is($source))->toBeTrue();
    expect($source->runs()->sole()->is($run))->toBeTrue();
    expect($run->only(IngestionRun::COUNTERS))->toBe(array_fill_keys(IngestionRun::COUNTERS, 0));
})->with(['scheduled', 'manual', 'retry']);

it('finalizes each state once with counters and finish time', function (string $method, string $status) {
    $this->freezeSecond();
    $service = app(IngestionRunService::class);
    $run = $service->startRun(JobSource::factory()->create());
    $counters = ['discovered_count' => 10, 'fetched_count' => 8, 'created_count' => 3, 'updated_count' => 2, 'skipped_count' => 1, 'failed_count' => 2];
    $this->travel(1)->minutes();

    $finished = $service->{$method}($run, $counters);

    expect($finished->status)->toBe($status);
    expect($finished->finished_at->equalTo(now()))->toBeTrue();
    expect($finished->only(IngestionRun::COUNTERS))->toBe($counters);
    foreach (['markSucceeded', 'markPartial', 'markFailed'] as $next) {
        expect(fn () => $service->{$next}($run))->toThrow(ValidationException::class);
    }
    expect($run->fresh()->status)->toBe($status);
})->with([['markSucceeded', 'succeeded'], ['markPartial', 'partial'], ['markFailed', 'failed']]);

it('rejects inactive sources for every trigger without changing activation', function (string $trigger) {
    $source = JobSource::factory()->create(['is_active' => false]);

    expect(fn () => app(IngestionRunService::class)->startRun($source, $trigger))->toThrow(ValidationException::class);

    $this->assertDatabaseCount('ingestion_runs', 0);
    expect($source->fresh()->is_active)->toBeFalse();
})->with(['scheduled', 'manual', 'retry']);

it('rejects unknown triggers and invalid counters', function () {
    $source = JobSource::factory()->create();
    $service = app(IngestionRunService::class);
    expect(fn () => $service->startRun($source, 'command'))->toThrow(ValidationException::class);
    $run = $service->startRun($source);

    foreach ([['failed_count' => -1], ['created_count' => 4294967296], ['untrusted' => 1]] as $counters) {
        expect(fn () => $service->markSucceeded($run, $counters))->toThrow(ValidationException::class);
    }

    expect($run->fresh()->status)->toBe('running');
    expect($run->fresh()->finished_at)->toBeNull();
});

it('stores only safe diagnostics and rejects secrets traces and arbitrary error codes', function () {
    $service = app(IngestionRunService::class);
    $run = $service->startRun(JobSource::factory()->create());

    foreach (['authorization', 'cookie', 'api_key', 'exception', 'stack_trace'] as $key) {
        expect(fn () => $service->markFailed($run, errorContext: [$key => 'SECRET']))->toThrow(ValidationException::class);
    }
    expect(fn () => $service->markFailed($run, errorCode: 'SECRET'))->toThrow(ValidationException::class);
    expect(fn () => $service->markFailed($run, errorContext: ['http_status' => 'SECRET']))->toThrow(ValidationException::class);
    $finished = $service->markFailed($run, errorCode: 'parser_error', errorContext: ['http_status' => 200, 'parser_error_count' => 2]);

    expect($finished->error_message)->toBe('The source data could not be parsed.');
    expect($finished->error_context)->toBe(['http_status' => 200, 'parser_error_count' => 2]);
    expect(json_encode($finished->toArray()))->not->toContain('SECRET');
});

it('derives health by completion order with partial breaking failures and running excluded', function () {
    $this->freezeSecond();
    $source = JobSource::factory()->create();
    $service = app(IngestionRunService::class);
    $health = app(JobSourceHealthService::class);
    expect($health->forSource($source))->toBe([
        'total_runs' => 0, 'successful_runs' => 0, 'failed_runs' => 0, 'partial_runs' => 0,
        'empty_successful_runs' => 0, 'error_runs' => 0, 'last_run_at' => null, 'last_successful_run_at' => null, 'consecutive_failures' => 0,
    ]);
    $earlyRun = $service->startRun($source);
    $success = $service->markSucceeded($service->startRun($source));
    $service->markFailed($service->startRun($source));
    $service->markPartial($service->startRun($source));
    expect($health->forSource($source)['consecutive_failures'])->toBe(0);
    $service->markFailed($service->startRun($source));
    $this->travel(1)->minutes();
    $service->markFailed($earlyRun);
    $running = $service->startRun($source);

    expect($health->forSource($source))->toBe([
        'total_runs' => 6, 'successful_runs' => 1, 'failed_runs' => 3, 'partial_runs' => 1,
        'empty_successful_runs' => 1, 'error_runs' => 4, 'last_run_at' => $running->started_at->toISOString(),
        'last_successful_run_at' => $success->finished_at->toISOString(), 'consecutive_failures' => 2,
    ]);
    $service->markSucceeded($running, ['discovered_count' => 1]);
    expect($health->forSource($source))->toMatchArray(['successful_runs' => 2, 'empty_successful_runs' => 1, 'consecutive_failures' => 0]);
});

it('isolates failures and rolled back transitions from other source runs and health', function () {
    $sourceA = JobSource::factory()->create();
    $sourceB = JobSource::factory()->create();
    $service = app(IngestionRunService::class);
    $health = app(JobSourceHealthService::class);
    $runA = $service->startRun($sourceA);
    $runB = $service->startRun($sourceB);
    $beforeB = $health->forSource($sourceB);

    $service->markFailed($runA, ['failed_count' => 3]);

    expect($health->forSource($sourceB))->toBe($beforeB);
    expect($runB->fresh()->status)->toBe('running');
    expect($runB->fresh()->failed_count)->toBe(0);
    expect(fn () => DB::transaction(function () use ($service, $runB): void {
        $service->markSucceeded($runB);
        throw new RuntimeException('rollback');
    }))->toThrow(RuntimeException::class);
    expect($health->forSource($sourceB))->toBe($beforeB);
    $service->markSucceeded($runB, ['created_count' => 1]);
    expect($runA->fresh()->status)->toBe('failed');
});

it('allows only active administrators to view source health and filtered runs', function (string $role, bool $active, int $status) {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
    $source = JobSource::factory()->create();
    $service = app(IngestionRunService::class);
    $run = $service->markFailed($service->startRun($source, 'retry'));
    $service->startRun($source, 'manual');
    $service->startRun(JobSource::factory()->create());
    $user = User::factory()->create(['role' => $role, 'is_active' => $active]);
    $this->withToken(JWTAuth::fromUser($user));

    $details = $this->getJson("/api/admin/job-sources/{$source->id}")->assertStatus($status);
    $runs = $this->getJson("/api/admin/job-sources/{$source->id}/runs?status=failed&trigger_type=retry&per_page=1")->assertStatus($status);

    if ($status === 200) {
        $details->assertJsonPath('health.total_runs', 2)->assertJsonPath('health.failed_runs', 1);
        $runs->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $run->id)->assertJsonPath('meta.total', 1);
        $this->getJson("/api/admin/job-sources/{$source->id}/runs?per_page=1")->assertOk()->assertJsonPath('meta.total', 2);
        $this->getJson("/api/admin/job-sources/{$source->id}/runs?status=unknown&trigger_type=bad&per_page=101")
            ->assertUnprocessable()->assertJsonValidationErrors(['status', 'trigger_type', 'per_page']);
    }
})->with([['admin', true, 200], ['super_admin', true, 200], ['candidate', true, 403], ['admin', false, 403], ['super_admin', false, 403]]);

it('requires authentication for operational reads', function () {
    $source = JobSource::factory()->create();

    $this->getJson("/api/admin/job-sources/{$source->id}/runs")->assertUnauthorized();
    $this->getJson("/api/admin/job-sources/{$source->id}")->assertUnauthorized();
});

it('localizes lifecycle and diagnostic messages while retaining machine values', function (string $locale, string $inactive, string $finished, string $failure) {
    app()->setLocale($locale);
    $source = JobSource::factory()->create(['is_active' => false]);
    $service = app(IngestionRunService::class);

    expect(fn () => $service->startRun($source))->toThrow(ValidationException::class, $inactive);
    $source->update(['is_active' => true]);
    $run = $service->markFailed($service->startRun($source, 'retry'));

    expect($run->error_message)->toBe($failure);
    expect($run->status)->toBe('failed');
    expect($run->trigger_type)->toBe('retry');
    expect($run->error_code)->toBe('collection_failed');
    expect(fn () => $service->markSucceeded($run))->toThrow(ValidationException::class, $finished);
})->with([
    ['en', 'Inactive sources cannot start ingestion runs.', 'This ingestion run has already finished.', 'The source collection failed.'],
    ['ar', 'لا يمكن بدء عمليات جمع البيانات للمصادر غير النشطة.', 'انتهت عملية جمع البيانات هذه بالفعل.', 'فشلت عملية جمع البيانات من المصدر.'],
]);
