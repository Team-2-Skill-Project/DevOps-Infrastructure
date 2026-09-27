<?php

namespace Database\Factories;

use App\Models\Roadmap;
use App\Models\RoadmapStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RoadmapStep> */
class RoadmapStepFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'roadmap_id' => Roadmap::factory(),
            'step_order' => 1,
            'title' => fake()->sentence(3),
        ];
    }
}
