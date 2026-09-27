<?php

namespace Database\Factories;

use App\Models\IngestionRun;
use App\Models\JobPost;
use App\Models\JobSourceReference;
use App\Models\RawJob;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JobSourceReference> */
class JobSourceReferenceFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'job_post_id' => JobPost::factory(),
            'raw_job_id' => RawJob::factory(),
            'job_source_id' => fn (array $attributes): int => RawJob::query()->whereKey($attributes['raw_job_id'])->firstOrFail()->job_source_id,
            'ingestion_run_id' => fn (array $attributes): int => RawJob::query()->whereKey($attributes['raw_job_id'])->firstOrFail()->ingestion_run_id,
            'source_url' => 'https://example.com/jobs/'.fake()->uuid(),
            'match_method' => 'existing_reference',
        ];
    }

    public function forProvenance(JobPost $jobPost, RawJob $rawJob): static
    {
        return $this->state(fn (): array => [
            'job_post_id' => $jobPost->id,
            'raw_job_id' => $rawJob->id,
            'job_source_id' => $rawJob->job_source_id,
            'ingestion_run_id' => $rawJob->ingestion_run_id,
            'external_id' => $rawJob->external_id,
            'source_url' => $rawJob->source_url,
            'detail_url' => $rawJob->detail_url,
        ]);
    }

    public function forRun(IngestionRun $run): static
    {
        return $this->state(fn (): array => [
            'job_source_id' => $run->job_source_id,
            'ingestion_run_id' => $run->id,
        ]);
    }
}
