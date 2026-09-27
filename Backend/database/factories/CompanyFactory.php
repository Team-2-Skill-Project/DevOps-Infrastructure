<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'normalized_name' => Str::lower($name).'-'.fake()->unique()->numberBetween(1, 999999),
            'website_url' => fake()->url(),
            'linkedin_url' => fake()->url(),
            'logo_url' => fake()->imageUrl(),
            'industry' => fake()->word(),
            'country' => fake()->country(),
            'state' => fake()->city(),
            'city' => fake()->city(),
            'description' => fake()->paragraph(),
            'is_verified' => false,
            'is_active' => true,
            'created_by' => null,
        ];
    }
}
