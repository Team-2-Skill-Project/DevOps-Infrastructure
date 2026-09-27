<?php

namespace Database\Factories;

use App\Models\CandidateProfile;
use App\Models\Experience;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Experience> */
class ExperienceFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'candidate_profile_id' => CandidateProfile::factory(),
            'job_title' => fake()->jobTitle(),
            'company_name' => fake()->company(),
            'source' => Experience::SOURCE_MANUAL,
        ];
    }
}
