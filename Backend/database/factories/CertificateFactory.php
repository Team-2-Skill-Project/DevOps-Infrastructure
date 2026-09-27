<?php

namespace Database\Factories;

use App\Models\CandidateProfile;
use App\Models\Certificate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Certificate> */
class CertificateFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'candidate_profile_id' => CandidateProfile::factory(),
            'name' => fake()->sentence(3),
            'source' => Certificate::SOURCE_MANUAL,
        ];
    }
}
