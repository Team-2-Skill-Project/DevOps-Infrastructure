<?php

namespace Database\Factories;

use App\Models\Skill;
use App\Models\SkillAlias;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<SkillAlias> */
class SkillAliasFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $alias = fake()->unique()->sentence(3, false);

        return [
            'skill_id' => Skill::factory(),
            'alias' => $alias,
            'normalized_alias' => Str::lower(Str::trim($alias)),
        ];
    }
}
