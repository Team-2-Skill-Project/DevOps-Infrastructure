<?php

namespace Database\Factories;

use App\Models\CandidateProfile;
use App\Models\Education;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Education> */
class EducationFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'candidate_profile_id' => CandidateProfile::factory(),
            'institution' => fake()->company(),
            'degree' => 'Bachelor',
            'source' => Education::SOURCE_MANUAL,
        ];
    }
}
