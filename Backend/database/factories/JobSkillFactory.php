<?php

namespace Database\Factories;

use App\Models\JobPost;
use App\Models\JobSkill;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobSkill>
 */
class JobSkillFactory extends Factory
{
    public function definition(): array
    {
        return [
            'job_post_id' => JobPost::factory(),
            'skill_id' => Skill::factory(),
            'is_required' => true,
            'importance' => fake()->numberBetween(1, 5),
        ];
    }
}
