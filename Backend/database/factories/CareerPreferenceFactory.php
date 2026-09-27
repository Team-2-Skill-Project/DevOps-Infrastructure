<?php

namespace Database\Factories;

use App\Models\CandidateProfile;
use App\Models\CareerPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CareerPreference> */
class CareerPreferenceFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'candidate_profile_id' => CandidateProfile::factory(),
            'target_role' => fake()->jobTitle(),
        ];
    }
}
