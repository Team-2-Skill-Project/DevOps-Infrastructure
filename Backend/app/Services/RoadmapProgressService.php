<?php

namespace App\Services;

use App\Enums\RoadmapStatus;
use App\Enums\RoadmapTaskStatus;
use App\Models\CandidateProfile;
use App\Models\Roadmap;
use App\Models\RoadmapTask;
use Illuminate\Support\Facades\DB;

class RoadmapProgressService
{
    /** @param array{evidence?: string|null, notes?: string|null} $data */
    public function completeTask(CandidateProfile $profile, int $taskId, array $data): Roadmap
    {
        return DB::transaction(function () use ($profile, $taskId, $data): Roadmap {
            $task = RoadmapTask::query()
                ->whereKey($taskId)
                ->whereHas('roadmap', fn ($query) => $query->where('candidate_profile_id', $profile->id))
                ->lockForUpdate()
                ->firstOrFail();

            $attributes = [];
            if (! $task->isCompleted()) {
                $attributes['status'] = RoadmapTaskStatus::COMPLETED->value;
                $attributes['completed_at'] = now();
            }
            foreach (['evidence', 'notes'] as $field) {
                if (array_key_exists($field, $data)) {
                    $attributes[$field] = $data[$field];
                }
            }
            if ($attributes !== []) {
                $task->update($attributes);
            }

            $roadmap = Roadmap::query()->whereKey($task->roadmap_id)->lockForUpdate()->firstOrFail();

            return $this->recalculate($roadmap);
        }, 3);
    }

    public function recalculate(Roadmap $roadmap): Roadmap
    {
        $roadmap->load(['phases.milestones.tasks']);
        $roadmapTaskCount = 0;
        $roadmapCompletedCount = 0;

        foreach ($roadmap->phases as $phase) {
            $phaseTaskCount = 0;
            $phaseCompletedCount = 0;

            foreach ($phase->milestones as $milestone) {
                $taskCount = $milestone->tasks->count();
                $completedCount = $milestone->tasks
                    ->filter(fn (RoadmapTask $task): bool => $task->isCompleted())
                    ->count();
                $progress = $this->percentage($completedCount, $taskCount);

                $milestone->update([
                    'progress' => $progress,
                    'completed_at' => $progress === 100.0 ? ($milestone->completed_at ?? now()) : null,
                ]);

                $phaseTaskCount += $taskCount;
                $phaseCompletedCount += $completedCount;
            }

            $phase->update(['progress' => $this->percentage($phaseCompletedCount, $phaseTaskCount)]);
            $roadmapTaskCount += $phaseTaskCount;
            $roadmapCompletedCount += $phaseCompletedCount;
        }

        $progress = $this->percentage($roadmapCompletedCount, $roadmapTaskCount);
        $attributes = ['overall_progress' => $progress];
        if ($roadmap->status !== RoadmapStatus::ARCHIVED->value) {
            $attributes['status'] = $roadmapTaskCount > 0 && $progress === 100.0
                ? RoadmapStatus::COMPLETED->value
                : RoadmapStatus::ACTIVE->value;
        }
        $roadmap->update($attributes);

        return $roadmap->refresh()->load(self::hierarchyRelations());
    }

    /** @return list<string> */
    public static function hierarchyRelations(): array
    {
        return [
            'targetJobPost.company:id,name',
            'phases.milestones.tasks',
        ];
    }

    private function percentage(int $completed, int $total): float
    {
        return $total === 0 ? 0.0 : round(($completed / $total) * 100, 2);
    }
}
