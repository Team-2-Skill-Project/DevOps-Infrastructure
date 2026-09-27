<?php

namespace Database\Factories;

use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CandidateSkill> */
class CandidateSkillFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'candidate_profile_id' => CandidateProfile::factory(),
            'skill_id' => Skill::factory(),
            'source' => CandidateSkill::SOURCE_MANUAL,
        ];
    }
}
