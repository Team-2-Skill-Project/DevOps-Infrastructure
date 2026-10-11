<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\Experience;
use App\Models\JobMatch;
use App\Models\JobPost;
use App\Models\JobSkill;
use App\Models\Project;

class JobMatchingService extends BaseAiService
{
    /**
     * حساب التوافق وتحليل الفجوات وتخزين النتائج
     */
    public function calculateAndPersistMatch(CandidateProfile $profile, JobPost $job): JobMatch
    {
        $profile->load(['candidateSkills.skill', 'experiences', 'projects', 'careerPreference', 'user']);
        $job->load(['jobSkills.skill', 'company']);

        $response = $this->postJson('/api/v1/matches/analyze', [
            'job_id' => (string) $job->id,
            'candidate_id' => (string) $profile->user_id,
            'job_requirements' => [
                'job_id' => (string) $job->id,
                'role_title' => $job->title,
                'skills' => $this->jobSkillPayload($job),
                'summary' => $job->description,
            ],
            'candidate_profile' => [
                'candidate_id' => (string) $profile->user_id,
                'name' => $profile->user?->name,
                'skills' => $this->candidateSkillPayload($profile),
                'work_history' => $this->experiencePayload($profile),
                'projects' => $this->projectPayload($profile),
                'target_roles' => $this->targetRoles($profile),
                'preferences' => $this->preferencePayload($profile),
            ],
            'preferred_skills' => $this->preferredSkillPayload($job),
            'job_title' => $job->title,
            'canonical_role' => $job->canonical_role,
            'min_years_experience' => $job->min_years_experience,
            'work_mode' => $job->work_mode,
            'location' => $job->city ?? $job->country,
            'employment_type' => $job->employment_type,
        ]);

        if (! is_numeric($response['overall_match_score'] ?? null)
            || ! is_string($response['qualification_status'] ?? null)
            || ! is_string($response['full_candidate_summary'] ?? null)
            || ! is_array($response['skill_breakdown'] ?? null)) {
            $this->invalidResponse();
        }

        $missingSkills = array_values(array_unique([
            ...$this->stringList($response['missing_critical_skills'] ?? []),
            ...$this->stringList($response['nice_to_have_gaps'] ?? []),
        ]));
        $weakSkills = $this->stringList($response['weak_skills'] ?? []);
        $reasons = array_filter([
            'qualification_status' => $response['qualification_status'],
            'summary' => $response['full_candidate_summary'],
            'rationale' => is_string($response['rationale'] ?? null) ? $response['rationale'] : null,
            'priority' => is_string($response['priority'] ?? null) ? $response['priority'] : null,
        ], static fn (mixed $value): bool => $value !== null);

        return JobMatch::updateOrCreate(
            ['candidate_profile_id' => $profile->id, 'job_post_id' => $job->id],
            [
                'score' => $response['overall_match_score'],
                'matched_skills' => $response['skill_breakdown'],
                'missing_skills' => $missingSkills,
                'weak_skills' => $weakSkills,
                'reasons' => $reasons,
                'calculated_at' => now(),
            ]
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function jobSkillPayload(JobPost $job): array
    {
        return array_values($job->jobSkills
            ->map(fn (JobSkill $jobSkill): ?array => $this->skillRequirement($jobSkill))
            ->filter()
            ->values()
            ->all());
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function preferredSkillPayload(JobPost $job): array
    {
        return array_values($job->jobSkills
            ->filter(fn (JobSkill $jobSkill): bool => ! $jobSkill->is_required)
            ->map(fn (JobSkill $jobSkill): ?array => $this->skillRequirement($jobSkill))
            ->filter()
            ->values()
            ->all());
    }

    /**
     * @return array<string, mixed>|null
     */
    private function skillRequirement(JobSkill $jobSkill): ?array
    {
        $skill = $jobSkill->skill;

        if ($skill === null) {
            return null;
        }

        $skillName = trim($skill->name);

        if ($skillName === '') {
            return null;
        }

        $payload = [
            'skill_id' => (string) $jobSkill->skill_id,
            'skill_name' => $skillName,
            'is_critical' => (bool) $jobSkill->is_required,
        ];

        if ($jobSkill->required_level !== null && trim($jobSkill->required_level) !== '') {
            $payload['proficiency'] = $jobSkill->required_level;
        }

        if (is_string($skill->category) && trim($skill->category) !== '') {
            $payload['category'] = $skill->category;
        }

        return $payload;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function candidateSkillPayload(CandidateProfile $profile): array
    {
        return array_values($profile->candidateSkills
            ->map(function (CandidateSkill $candidateSkill): ?array {
                $skill = $candidateSkill->skill;

                if ($skill === null) {
                    return null;
                }

                $skillName = trim($skill->name);

                if ($skillName === '') {
                    return null;
                }

                $payload = ['skill_name' => $skillName];
                if ($candidateSkill->proficiency_level !== null && trim($candidateSkill->proficiency_level) !== '') {
                    $payload['proficiency'] = $candidateSkill->proficiency_level;
                }

                return $payload;
            })
            ->filter()
            ->values()
            ->all());
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function experiencePayload(CandidateProfile $profile): array
    {
        return array_values($profile->experiences
            ->map(fn (Experience $experience): array => [
                'role' => $experience->job_title,
                'company' => $experience->company_name,
                'duration' => $this->experienceDuration($experience),
                'description' => $experience->description,
            ])
            ->all());
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function projectPayload(CandidateProfile $profile): array
    {
        return array_values($profile->projects
            ->map(fn (Project $project): array => [
                'project_name' => $project->name,
                'description' => $project->description,
                'technologies' => $this->stringList($project->getAttribute('technologies')),
            ])
            ->all());
    }

    /**
     * @return list<string>
     */
    private function targetRoles(CandidateProfile $profile): array
    {
        $preference = $profile->careerPreference;
        $roles = [$preference?->target_role];
        $targetRoles = $preference?->getAttribute('target_roles');

        if (is_array($targetRoles)) {
            $roles = [...$roles, ...$targetRoles];
        }

        return array_values(array_unique($this->stringList($roles)));
    }

    /** @return array<string, list<string>> */
    private function preferencePayload(CandidateProfile $profile): array
    {
        $preference = $profile->careerPreference;

        return [
            'employment_type' => $this->stringList([$preference?->job_type]),
            'work_mode' => $this->stringList([$preference?->work_mode]),
            'locations' => $this->stringList([$preference?->preferred_country, $preference?->preferred_city]),
            'industries' => $this->stringList($preference?->preferred_industries),
        ];
    }

    private function experienceDuration(Experience $experience): ?string
    {
        $startDate = $experience->getAttribute('start_date');
        $endDate = $experience->getAttribute('end_date');
        $start = $startDate instanceof \DateTimeInterface ? $startDate->format('Y-m-d') : null;
        $end = (bool) $experience->is_current
            ? 'present'
            : ($endDate instanceof \DateTimeInterface ? $endDate->format('Y-m-d') : null);

        if ($start === null && $end === null) {
            return null;
        }

        return trim(($start ?? '').' - '.($end ?? 'present'));
    }

    /** @return list<string> */
    private function stringList(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        return array_values(array_filter(
            $values,
            static fn (mixed $value): bool => is_string($value) && trim($value) !== '',
        ));
    }
}
