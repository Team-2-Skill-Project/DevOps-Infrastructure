<?php

namespace Database\Factories;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Skill> */
class SkillFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = rtrim(fake()->unique()->sentence(3, false), '.');

        return [
            'name' => $name,
            'normalized_name' => Str::lower($name),
        ];
    }
}
