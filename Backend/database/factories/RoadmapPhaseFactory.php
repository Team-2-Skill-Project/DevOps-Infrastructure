<?php

namespace Database\Factories;

use App\Models\Roadmap;
use App\Models\RoadmapPhase;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RoadmapPhase> */
class RoadmapPhaseFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'roadmap_id' => Roadmap::factory(),
            'phase_order' => 1,
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'rationale' => null,
            'progress' => 0,
        ];
    }
}
