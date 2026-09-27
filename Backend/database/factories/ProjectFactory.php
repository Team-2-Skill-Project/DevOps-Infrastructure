<?php

namespace Database\Factories;

use App\Models\CandidateProfile;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Project> */
class ProjectFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'candidate_profile_id' => CandidateProfile::factory(),
            'name' => fake()->sentence(3),
            'source' => Project::SOURCE_MANUAL,
        ];
    }
}
