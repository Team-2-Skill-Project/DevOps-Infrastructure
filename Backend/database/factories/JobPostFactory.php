<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\JobPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobPost>
 */
class JobPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'created_by' => null,
            'title' => fake()->jobTitle(),
            'job_type' => JobPost::TYPE_JOB,
            'description' => fake()->paragraph(),
            'employment_type' => 'full_time',
            'work_mode' => 'onsite',
            'experience_level' => 'entry',
            'country' => fake()->country(),
            'state' => fake()->city(),
            'city' => fake()->city(),
            'salary_min' => null,
            'salary_max' => null,
            'salary_currency' => null,
            'application_url' => fake()->url(),
            'application_method' => JobPost::APPLICATION_EXTERNAL,
            'source' => 'manual',
            'external_id' => null,
            'external_url' => null,
            'status' => 'draft',
            'is_active' => true,
            'published_at' => null,
            'expires_at' => null,
        ];
    }
}
