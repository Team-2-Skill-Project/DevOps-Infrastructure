<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\CompanyAlias;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CompanyAlias>
 */
class CompanyAliasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $alias = fake()->unique()->company();

        return [
            'company_id' => Company::factory(),
            'alias' => $alias,
            'normalized_alias' => Str::lower($alias),
        ];
    }
}
