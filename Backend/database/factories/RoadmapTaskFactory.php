<?php

namespace Database\Factories;

use App\Enums\RoadmapTaskStatus;
use App\Models\RoadmapMilestone;
use App\Models\RoadmapTask;
use Illuminate\Database\Eloquent\Factories\Factory;
use LogicException;

/** @extends Factory<RoadmapTask> */
class RoadmapTaskFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'roadmap_milestone_id' => RoadmapMilestone::factory(),
            'roadmap_id' => fn (array $attributes): int => $this->roadmapIdForMilestone((int) $attributes['roadmap_milestone_id']),
            'step_order' => 1,
            'task_order' => 1,
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'status' => RoadmapTaskStatus::PENDING->value,
        ];
    }

    private function roadmapIdForMilestone(int $milestoneId): int
    {
        $roadmapId = RoadmapMilestone::query()
            ->whereKey($milestoneId)
            ->join('roadmap_phases', 'roadmap_phases.id', '=', 'roadmap_milestones.roadmap_phase_id')
            ->value('roadmap_phases.roadmap_id');

        if (! is_int($roadmapId)) {
            throw new LogicException('Roadmap task factories require a persisted milestone hierarchy.');
        }

        return $roadmapId;
    }
}
