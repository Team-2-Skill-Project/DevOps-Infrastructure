<?php

namespace App\Services;

use App\Models\IngestionRun;
use App\Models\JobSource;
use Illuminate\Support\Facades\DB;

class JobSourceHealthService
{
    /**
     * Completion order is finished_at then id. Running runs are excluded from the failure chain;
     * partial and succeeded runs break it. Empty means succeeded with zero discovered jobs.
     * Error runs count completed runs with an error code, not individual parser failures.
     *
     * @return array<string, mixed>
     */
    public function forSource(JobSource $source): array
    {
        return DB::transaction(function () use ($source): array {
            $counts = DB::table('ingestion_runs')->where('job_source_id', $source->id)
                ->selectRaw("COUNT(*) as total_runs,
                    COALESCE(SUM(CASE WHEN status = 'succeeded' THEN 1 ELSE 0 END), 0) as successful_runs,
                    COALESCE(SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END), 0) as failed_runs,
                    COALESCE(SUM(CASE WHEN status = 'partial' THEN 1 ELSE 0 END), 0) as partial_runs,
                    COALESCE(SUM(CASE WHEN status = 'succeeded' AND discovered_count = 0 THEN 1 ELSE 0 END), 0) as empty_successful_runs,
                    COALESCE(SUM(CASE WHEN status != 'running' AND error_code IS NOT NULL THEN 1 ELSE 0 END), 0) as error_runs")
                ->sole();
            $consecutiveFailures = $source->runs()->where('status', '!=', IngestionRun::STATUS_RUNNING)
                ->orderByDesc('finished_at')->orderByDesc('id')->cursor()
                ->takeWhile(fn (IngestionRun $run): bool => $run->status === IngestionRun::STATUS_FAILED)->count();

            return [
                'total_runs' => (int) $counts->total_runs,
                'successful_runs' => (int) $counts->successful_runs,
                'failed_runs' => (int) $counts->failed_runs,
                'partial_runs' => (int) $counts->partial_runs,
                'empty_successful_runs' => (int) $counts->empty_successful_runs,
                'error_runs' => (int) $counts->error_runs,
                'last_run_at' => $source->runs()->orderByDesc('started_at')->orderByDesc('id')->first()?->started_at?->toISOString(),
                'last_successful_run_at' => $source->runs()->where('status', IngestionRun::STATUS_SUCCEEDED)
                    ->orderByDesc('finished_at')->orderByDesc('id')->first()?->finished_at?->toISOString(),
                'consecutive_failures' => $consecutiveFailures,
            ];
        });
    }
}
