<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Models\CandidateProfile;
use App\Models\JobMatch;
use App\Models\JobPost;

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
                'work_mode' => $profile->preferred_work_mode,
                'location' => $profile->location,
            ],
        ];

        $response = $this->sendRequest('jobs/match', $payload);
        $output = $response['output'];

        // حفظ أو تحديث نتيجة المطابقة (Match Result & Gap Persistence)
        return JobMatch::updateOrCreate(
            ['user_id' => $profile->user_id, 'job_id' => $job->id],
            [
                'match_score' => $output['match_score'],
                'match_level' => $output['match_level'],
                'explanation' => $output['explanation'],
                'matched_skills' => json_encode($output['matched_skills']),
                'missing_critical_skills' => json_encode($output['missing_critical_skills']),
                'missing_optional_skills' => json_encode($output['missing_optional_skills']),
            ]
        );
    }
}
