<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Models\CandidateProfile;
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

        // Prevent phpstan undefined property warnings when CandidateProfile phpdoc may be missing
        /** @var CandidateProfile|null $profile */

        // Fallback لو بيانات الـ Profile أو الـ CV ناقصة تماماً
        if (! $profile || ! $profile->target_role) {
            throw ValidationException::withMessages([
                'context' => __('ai.missing_profile_context') ?: 'عذراً، يجيب إكمال الملف المهني وتحديد الوظيفة المستهدفة أولاً لكي يستطيع المرشد مساعدتك بدقة.',
            ]);
        }

        // بناء الـ Context الشامل للمرشد الذكي
        $context = [
            'verified_profile' => $profile->toArray(),
            'target_role' => $profile->target_role,
            'saved_jobs' => $user->savedJobs()->with('job')->get()->toArray(),
            'applied_jobs' => $user->jobApplications()->with('job')->get()->toArray(),
            'skill_gaps' => $profile->skillGaps ?? [],
            'roadmap' => $user->roadmaps()->with('steps')->first()?->toArray() ?? [],
        ];

        $payload = [
            'context' => $context,
            'job_focus_id' => $jobId,
            'question' => $question,
        ];

        // استدعاء خدمة الـ AI Mentor
        $response = $this->sendRequest('mentor/chat', $payload);
        $output = $response['output'];

        // تخزين المحادثة والرسائل (Mentor Conversation Storage)
        $chat = MentorChat::firstOrCreate(
            ['user_id' => $user->id, 'job_id' => $jobId],
            ['title' => mb_substr($question, 0, 50)]
        );

        // حفظ رسالة المستخدم
        MentorMessage::create([
            'mentor_chat_id' => $chat->id,
            'sender' => 'user',
            'content' => $question,
        ]);

        // حفظ رد المرشد الذكي مع الروابط والأفعال المدعومة
        $mentorMessage = MentorMessage::create([
            'mentor_chat_id' => $chat->id,
            'sender' => 'mentor',
            'content' => $output['answer'],
            'supported_actions' => json_encode($output['actions'] ?? []),
        ]);

        return [
            'chat_id' => $chat->id,
            'answer' => $output['answer'],
            'actions' => $output['actions'] ?? [],
            'confidence' => $response['confidence'],
        ];
    }
}
