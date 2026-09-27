<?php

namespace Database\Factories;

use App\Models\SkillCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<SkillCategory> */
class SkillCategoryFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->sentence(3, false);

        return ['name' => $name, 'normalized_name' => Str::lower(Str::trim($name))];
    }
}
