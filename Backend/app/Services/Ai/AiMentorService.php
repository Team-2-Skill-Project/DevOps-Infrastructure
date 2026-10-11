<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Models\MentorChat;
use App\Models\MentorMessage;
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

        $latestMatch = $profile->jobMatches()->latest('calculated_at')->firstOrNew([]);

        $context = [
            'verified_profile' => $profile->toArray(),
            'target_role' => $targetRole,
            'saved_jobs' => $user->savedJobs()->with('jobPost')->get()->toArray(),
            'applied_jobs' => $user->jobApplications()->with('job')->get()->toArray(),
            'skill_gaps' => $latestMatch->missing_skills ?? [],
            'roadmap' => $profile->roadmaps()->with('steps')->first()?->toArray() ?? [],
        ];

        $payload = [
            'context' => $context,
            'job_focus_id' => $jobId,
            'question' => $question,
        ];

        $response = $this->sendRequest('mentor/chat', $payload);
        $output = $response['output'] ?? [];

        if (! is_array($output) || ! isset($output['answer']) || ! is_string($output['answer'])) {
            throw ValidationException::withMessages([
                'ai' => __('ai.invalid_response'),
            ]);
        }

        $chat = MentorChat::firstOrCreate(
            ['user_id' => $user->id, 'job_id' => $jobId],
            ['title' => mb_substr($question, 0, 50)]
        );

        MentorMessage::create([
            'mentor_chat_id' => $chat->id,
            'sender' => 'user',
            'content' => $question,
        ]);

        MentorMessage::create([
            'mentor_chat_id' => $chat->id,
            'sender' => 'mentor',
            'content' => $output['answer'],
            'supported_actions' => $output['actions'] ?? [],
        ]);

        return [
            'chat_id' => $chat->id,
            'answer' => $output['answer'],
            'actions' => $output['actions'] ?? [],
            'confidence' => $response['confidence'],
        ];
    }
}
