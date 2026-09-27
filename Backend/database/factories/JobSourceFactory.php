<?php

namespace Database\Factories;

use App\Models\JobSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JobSource> */
class JobSourceFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(),
            'base_url' => fake()->url(),
            'source_type' => 'public',
            'collection_method' => 'api',
        ];
    }
}
