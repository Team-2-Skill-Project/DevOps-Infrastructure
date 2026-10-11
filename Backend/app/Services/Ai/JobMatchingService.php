<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Models\CandidateProfile;
use App\Models\JobMatch;
use App\Models\JobPost;
use Illuminate\Validation\ValidationException;

class JobMatchingService extends BaseAiService
{
    /**
     * حساب التوافق وتحليل الفجوات وتخزين النتائج
     */
    public function calculateAndPersistMatch(CandidateProfile $profile, JobPost $job): JobMatch
    {
        // Clarify $profile properties for static analysis
        /** @var CandidateProfile $profile */
        $payload = [
            'candidate_profile' => $profile->load(['skills', 'experiences', 'projects']),
            'job_requirements' => $job->load(['jobSkills.skill']),
            'preferences' => [
                'work_mode' => $profile->careerPreference?->work_mode,
                'location' => $profile->city ?? $profile->country,
            ],
        ];

        $response = $this->sendRequest('jobs/match', $payload);
        $output = $response['output'] ?? [];

        if (! is_array($output) || ! array_key_exists('match_score', $output)) {
            throw ValidationException::withMessages([
                'ai' => __('ai.invalid_response'),
            ]);
        }

        $missingSkills = $output['missing_skills']
            ?? array_merge($output['missing_critical_skills'] ?? [], $output['missing_optional_skills'] ?? []);
        $reasons = $output['reasons'] ?? null;

        if ($reasons === null && isset($output['explanation'])) {
            $reasons = ['explanation' => $output['explanation']];
        }

        return JobMatch::updateOrCreate(
            ['candidate_profile_id' => $profile->id, 'job_post_id' => $job->id],
            [
                'score' => $output['match_score'],
                'matched_skills' => $output['matched_skills'] ?? [],
                'missing_skills' => is_array($missingSkills) ? $missingSkills : [],
                'weak_skills' => is_array($output['weak_skills'] ?? null)
                    ? $output['weak_skills']
                    : [],
                'reasons' => is_array($reasons) ? $reasons : [],
                'confidence' => is_numeric($response['confidence'] ?? null) ? $response['confidence'] : null,
                'calculated_at' => now(),
            ]
        );
    }
}
