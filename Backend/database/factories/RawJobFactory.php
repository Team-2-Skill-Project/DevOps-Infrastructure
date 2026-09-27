<?php

namespace Database\Factories;

use App\Models\IngestionRun;
use App\Models\RawJob;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RawJob> */
class RawJobFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'ingestion_run_id' => IngestionRun::factory(),
            'job_source_id' => fn (array $attributes): int => IngestionRun::query()->whereKey($attributes['ingestion_run_id'])->firstOrFail()->job_source_id,
            'source_url' => 'https://example.com/jobs/'.fake()->uuid(),
            'identity_key' => fn (array $attributes): string => hash('sha256', 'url:'.$attributes['source_url']),
            'discovered_at' => now(),
        ];
    }
}
