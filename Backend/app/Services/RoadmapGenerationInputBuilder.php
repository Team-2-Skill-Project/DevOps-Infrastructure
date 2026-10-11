<?php

namespace App\Services;

use App\Enums\CvParsingStatus;
use App\Models\CandidateProfile;
use App\Models\JobMatch;
use App\Models\JobPost;
use App\Models\JobSkill;
use App\Models\Roadmap;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class RoadmapGenerationInputBuilder
{
    /**
     * @return array{baseline: array<string, mixed>, priority_gaps: list<array<string, mixed>>, target: array<string, mixed>}
     */
    public function build(
        CandidateProfile $profile,
        string $targetType,
        ?string $targetRole = null,
        ?int $targetJobId = null,
        ?Roadmap $roadmap = null,
    ): array {
        $profile->loadMissing(['candidateSkills.skill', 'experiences']);

        $targetJob = $targetType === 'job' ? $this->targetJob($targetJobId) : null;
        $target = $targetJob === null
            ? ['type' => 'role', 'role' => (string) $targetRole, 'title' => (string) $targetRole]
            : [
                'type' => 'job',
                'id' => $targetJob->id,
                'title' => $targetJob->title,
                'canonical_role' => $targetJob->canonical_role,
                'company' => $targetJob->company->name,
            ];

        return [
            'baseline' => $this->baseline($profile, $roadmap),
            'priority_gaps' => $targetJob === null ? [] : $this->priorityGaps($profile, $targetJob),
            'target' => $target,
        ];
    }

    /** @return array<string, mixed> */
    private function baseline(CandidateProfile $profile, ?Roadmap $roadmap): array
    {
        $currentCv = $profile->cvDocuments()->where('is_current', true)->latest('version')->first();
        $cvStatus = $currentCv?->getAttribute('status');
        $processedAt = $currentCv?->getAttribute('processed_at');

        return [
            'profile' => [
                'job_title' => $profile->job_title,
                'professional_summary' => $profile->professional_summary,
                'country' => $profile->country,
                'city' => $profile->city,
            ],
            'skills' => $profile->candidateSkills
                ->sortBy('skill_id')
                ->values()
                ->map(fn ($candidateSkill): array => [
                    'id' => (int) $candidateSkill->skill_id,
                    'name' => (string) $candidateSkill->skill->name,
                    'proficiency_level' => $candidateSkill->proficiency_level,
                    'source' => (string) $candidateSkill->source,
                    'confidence' => $candidateSkill->confidence,
                    'evidence' => $candidateSkill->evidence,
                ])->all(),
            'experience' => $profile->experiences
                ->sortBy('id')
                ->values()
                ->map(fn ($experience): array => [
                    'title' => (string) $experience->job_title,
                    'company_name' => (string) $experience->company_name,
                    'description' => $experience->description,
                    'technologies' => $experience->technologies,
                ])->all(),
            'cv' => $currentCv === null ? null : [
                'id' => $currentCv->id,
                'version' => $currentCv->version,
                'status' => $cvStatus instanceof CvParsingStatus ? $cvStatus->value : (string) $cvStatus,
                'processed_at' => $processedAt instanceof Carbon ? $processedAt->toISOString() : null,
            ],
            'roadmap_progress' => $roadmap === null ? 0.0 : (float) $roadmap->overall_progress,
        ];
    }

    private function targetJob(?int $targetJobId): JobPost
    {
        $job = JobPost::query()
            ->active()
            ->notExpired()
            ->whereKey($targetJobId)
            ->whereHas('company', fn ($query) => $query->where('is_active', true))
            ->with(['company:id,name', 'jobSkills.skill:id,name'])
            ->first();

        if ($job === null) {
            throw ValidationException::withMessages(['target_job_id' => __('roadmap.validation.target_job_unavailable')]);
        }

        return $job;
    }

    /** @return list<array<string, mixed>> */
    private function priorityGaps(CandidateProfile $profile, JobPost $job): array
    {
        $match = JobMatch::query()
            ->whereBelongsTo($profile)
            ->whereBelongsTo($job, 'jobPost')
            ->latest('calculated_at')
            ->latest('id')
            ->first();

        $jobSkillsById = $job->jobSkills->mapWithKeys(function (JobSkill $jobSkill): array {
            $skill = $jobSkill->skill;

            return $skill === null
                ? []
                : [$skill->id => $skill];
        });
        $gaps = $match === null ? [] : [
            ...$this->normalizeStoredGaps($match->getAttribute('missing_skills'), 'missing', $jobSkillsById),
            ...$this->normalizeStoredGaps($match->getAttribute('weak_skills'), 'weak', $jobSkillsById),
        ];

        if ($gaps !== []) {
            return $gaps;
        }

        $candidateSkillIds = $profile->candidateSkills->pluck('skill_id');
        $fallback = $job->jobSkills
            ->reject(fn (JobSkill $jobSkill): bool => $candidateSkillIds->contains($jobSkill->skill_id))
            ->values()
            ->map(fn (JobSkill $jobSkill): array => [
                'skill_id' => $jobSkill->skill_id,
                'name' => $jobSkill->skill->name,
                'kind' => 'missing',
                'importance' => $jobSkill->importance,
                'required_level' => $jobSkill->required_level,
            ])->all();

        return array_values($fallback);
    }

    /**
     * @param  Collection<covariant int, Skill>  $jobSkills
     * @return list<array<string, mixed>>
     */
    private function normalizeStoredGaps(mixed $storedGaps, string $kind, Collection $jobSkills): array
    {
        if (! is_array($storedGaps)) {
            return [];
        }

        $normalized = collect($storedGaps)
            ->filter(fn (mixed $gap): bool => is_array($gap))
            ->map(function (array $gap) use ($kind, $jobSkills): array {
                $skillId = isset($gap['skill_id']) ? (int) $gap['skill_id'] : null;
                $skill = $skillId === null ? null : $jobSkills->get($skillId);

                return [
                    ...$gap,
                    'skill_id' => $skillId,
                    'name' => $gap['name'] ?? ($skill === null ? __('roadmap.unknown_skill') : $skill->name),
                    'kind' => $kind,
                ];
            })->values()->all();

        return array_values($normalized);
    }
}
