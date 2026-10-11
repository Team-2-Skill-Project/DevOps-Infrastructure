<?php

namespace Database\Factories;

use App\Models\RoadmapMilestone;
use App\Models\RoadmapPhase;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RoadmapMilestone> */
class RoadmapMilestoneFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'roadmap_phase_id' => RoadmapPhase::factory(),
            'milestone_order' => 1,
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'progress' => 0,
            'completed_at' => null,
        ];
    }
}
