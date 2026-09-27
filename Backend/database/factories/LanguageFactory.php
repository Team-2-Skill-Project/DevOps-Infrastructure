<?php

namespace Database\Factories;

use App\Models\CandidateProfile;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Language> */
class LanguageFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'candidate_profile_id' => CandidateProfile::factory(),
            'language' => 'English',
            'proficiency_level' => 'B2',
            'source' => Language::SOURCE_MANUAL,
        ];
    }
}
