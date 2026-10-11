<?php

namespace App\Services;

use App\Contracts\RoadmapGeneratorContract;
use App\Enums\RoadmapStatus;
use App\Enums\RoadmapTaskStatus;
use App\Models\CandidateProfile;
use App\Models\Roadmap;
use App\Models\RoadmapTask;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoadmapGenerationService
{
    public function __construct(
        private RoadmapGeneratorContract $generator,
        private RoadmapGenerationInputBuilder $inputBuilder,
        private RoadmapOutputValidator $outputValidator,
        private RoadmapProgressService $progressService,
    ) {}

    public function generate(
        CandidateProfile $profile,
        string $targetType,
        ?string $targetRole = null,
        ?int $targetJobId = null,
        ?Roadmap $previousRoadmap = null,
    ): Roadmap {
        $input = $this->inputBuilder->build($profile, $targetType, $targetRole, $targetJobId, $previousRoadmap);
        $generated = $this->outputValidator->validate($this->generator->generate($input));
        $completedTasks = $this->completedTasksByTitle($previousRoadmap);

        return DB::transaction(function () use ($profile, $targetType, $targetRole, $targetJobId, $previousRoadmap, $input, $generated, $completedTasks): Roadmap {
            $lockedPrevious = $previousRoadmap === null ? null : Roadmap::query()
                ->whereKey($previousRoadmap->id)
                ->where('candidate_profile_id', $profile->id)
                ->lockForUpdate()
                ->firstOrFail();
            $targetTitle = (string) $input['target']['title'];

            $roadmap = Roadmap::query()->create([
                'previous_roadmap_id' => $lockedPrevious?->id,
                'candidate_profile_id' => $profile->id,
                'target_job_post_id' => $targetType === 'job' ? $targetJobId : null,
                'target_role' => $targetType === 'role' ? $targetRole : null,
                'title' => __('roadmap.generated_title', ['target' => $targetTitle]),
                'description' => null,
                'rationale' => $generated['rationale'],
                'overall_progress' => 0,
                'status' => RoadmapStatus::ACTIVE->value,
                'generation_version' => $this->generator->version(),
                'generated_at' => now(),
                'refreshed_at' => $lockedPrevious === null ? null : now(),
                'progress_at_generation' => 0,
                'generation_input_snapshot' => $input,
            ]);

            $stepOrder = 0;
            foreach ($generated['phases'] as $phaseData) {
                $phase = $roadmap->phases()->create([
                    'phase_order' => $phaseData['order'],
                    'title' => $phaseData['title'],
                    'description' => $phaseData['description'] ?? null,
                    'rationale' => $phaseData['rationale'] ?? null,
                ]);

                foreach ($phaseData['milestones'] as $milestoneData) {
                    $milestone = $phase->milestones()->create([
                        'milestone_order' => $milestoneData['order'],
                        'title' => $milestoneData['title'],
                        'description' => $milestoneData['description'] ?? null,
                    ]);

                    foreach ($milestoneData['tasks'] as $taskData) {
                        $task = $milestone->tasks()->create([
                            'roadmap_id' => $roadmap->id,
                            'step_order' => ++$stepOrder,
                            'task_order' => $taskData['order'],
                            'title' => $taskData['title'],
                            'description' => $taskData['description'] ?? null,
                            'status' => RoadmapTaskStatus::PENDING->value,
                            'metadata' => $taskData['metadata'] ?? null,
                        ]);

                        $this->carryCompletedTask($task, $completedTasks);
                    }
                }
            }

            if ($lockedPrevious !== null) {
                $lockedPrevious->update([
                    'status' => RoadmapStatus::ARCHIVED->value,
                    'refreshed_at' => now(),
                ]);
            }

            $roadmap = $this->progressService->recalculate($roadmap);
            $roadmap->update(['progress_at_generation' => $roadmap->overall_progress]);

            return $roadmap->refresh()->load(RoadmapProgressService::hierarchyRelations());
        }, 3);
    }

    /** @return array<string, RoadmapTask> */
    private function completedTasksByTitle(?Roadmap $roadmap): array
    {
        if ($roadmap === null) {
            return [];
        }

        return $roadmap->tasks()
            ->where('status', RoadmapTaskStatus::COMPLETED->value)
            ->get()
            ->keyBy(fn (RoadmapTask $task): string => $this->normalizedTitle($task->title))
            ->all();
    }

    /** @param array<string, RoadmapTask> $completedTasks */
    private function carryCompletedTask(RoadmapTask $task, array $completedTasks): void
    {
        $completedTask = $completedTasks[$this->normalizedTitle($task->title)] ?? null;
        if ($completedTask === null) {
            return;
        }

        $task->update([
            'status' => RoadmapTaskStatus::COMPLETED->value,
            'completed_at' => $completedTask->completed_at,
            'evidence' => $completedTask->evidence,
            'notes' => $completedTask->notes,
        ]);
    }

    private function normalizedTitle(string $title): string
    {
        return Str::lower(Str::squish($title));
    }
}
