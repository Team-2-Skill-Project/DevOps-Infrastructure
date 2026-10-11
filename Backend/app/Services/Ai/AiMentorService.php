<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class AiMentorService extends BaseAiService
{
    /**
     * بناء السياق والتحقق من اكتماله (Missing Context Handling & Fallback)
     */
    /**
     * @return array<string,mixed>
     */
    public function askMentor(User $user, ?int $jobId, string $question): array
    {
        $profile = $user->candidateProfile;

        if ($profile === null) {
            throw ValidationException::withMessages([
                'context' => __('ai.missing_profile_context'),
            ]);
        }

        $targetRole = data_get($profile->careerPreference, 'target_role') ?? $profile->job_title;

        if (! $targetRole) {
            throw ValidationException::withMessages([
                'context' => __('ai.missing_profile_context'),
            ]);
        }

        throw ValidationException::withMessages([
            'ai' => __('ai.mentor_contract_unavailable'),
        ]);
    }
}
