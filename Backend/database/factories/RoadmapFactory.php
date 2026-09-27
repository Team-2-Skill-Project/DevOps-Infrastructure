<?php

namespace Database\Factories;

use App\Models\CandidateProfile;
use App\Models\Roadmap;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Roadmap> */
class RoadmapFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'candidate_profile_id' => CandidateProfile::factory(),
            'title' => fake()->sentence(3),
        ];
    }
}
