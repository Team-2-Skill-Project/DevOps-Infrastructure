<?php

namespace App\Services;

use App\Enums\RoadmapStatus;
use App\Models\CandidateProfile;
use App\Models\Roadmap;

class RoadmapRefreshService
{
    public const PROGRESS_REFRESH_THRESHOLD = 25.0;

    public function __construct(
        private RoadmapGenerationInputBuilder $inputBuilder,
        private RoadmapGenerationService $generationService,
    ) {}

    /**
     * @return array{roadmap: Roadmap, refreshed: bool, reasons: list<string>}
     */
    public function refresh(
        CandidateProfile $profile,
        Roadmap $roadmap,
        ?string $targetType = null,
        ?string $targetRole = null,
        ?int $targetJobId = null,
    ): array {
        [$resolvedType, $resolvedRole, $resolvedJobId] = $this->resolveTarget($roadmap, $targetType, $targetRole, $targetJobId);
        $input = $this->inputBuilder->build($profile, $resolvedType, $resolvedRole, $resolvedJobId, $roadmap);
        $reasons = $this->reasons($roadmap, $input);

        if ($reasons === [] || $roadmap->status === RoadmapStatus::ARCHIVED->value) {
            return [
                'roadmap' => $roadmap->load(RoadmapProgressService::hierarchyRelations()),
                'refreshed' => false,
                'reasons' => [],
            ];
        }

        return [
            'roadmap' => $this->generationService->generate($profile, $resolvedType, $resolvedRole, $resolvedJobId, $roadmap),
            'refreshed' => true,
            'reasons' => $reasons,
        ];
    }

    /**
     * @param  array{baseline: array<string, mixed>, priority_gaps: list<array<string, mixed>>, target: array<string, mixed>}  $input
     * @return list<string>
     */
    private function reasons(Roadmap $roadmap, array $input): array
    {
        $snapshot = $roadmap->getAttribute('generation_input_snapshot');
        if (! is_array($snapshot)) {
            return ['missing_generation_snapshot'];
        }

        $reasons = [];
        if ($this->targetSignature($snapshot['target'] ?? null) !== $this->targetSignature($input['target'])) {
            $reasons[] = 'target_changed';
        }

        $snapshotSkills = is_array($snapshot['baseline'] ?? null) ? ($snapshot['baseline']['skills'] ?? null) : null;
        if ($snapshotSkills !== $input['baseline']['skills']) {
            $reasons[] = 'verified_skills_changed';
        }

        if ((float) $roadmap->overall_progress - (float) $roadmap->progress_at_generation >= self::PROGRESS_REFRESH_THRESHOLD) {
            $reasons[] = 'progress_threshold_reached';
        }

        return $reasons;
    }

    /** @return array{0: string, 1: string|null, 2: int|null} */
    private function resolveTarget(Roadmap $roadmap, ?string $type, ?string $role, ?int $jobId): array
    {
        if ($type !== null) {
            return [$type, $role, $jobId];
        }

        return $roadmap->target_job_post_id === null
            ? ['role', $roadmap->target_role, null]
            : ['job', null, (int) $roadmap->target_job_post_id];
    }

    private function targetSignature(mixed $target): string
    {
        if (! is_array($target)) {
            return '';
        }

        return ($target['type'] ?? '').':'.($target['id'] ?? $target['role'] ?? '');
    }
}
