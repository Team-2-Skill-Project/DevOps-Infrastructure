<?php

namespace Database\Factories;

use App\Models\IngestionRun;
use App\Models\JobSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<IngestionRun> */
class IngestionRunFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['job_source_id' => JobSource::factory(), 'trigger_type' => 'manual', 'status' => 'running', 'started_at' => now()];
    }
}
