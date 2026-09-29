<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiMentorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiMentorController extends Controller
{
    protected AiMentorService $aiMentorService;

    public function __construct(AiMentorService $aiMentorService)
    {
        $this->aiMentorService = $aiMentorService;
    }

    /**
     * إرسال سؤال للمرشد الذكي واستقبال الرد والسياق المدعوم
     */
    public function ask(Request $request): JsonResponse
    {
        $request->validate([
            'question' => 'required|string|max:1000',
            'job_id' => 'nullable|exists:jobs,id',
        ]);

        $user = $request->user();
        $question = $request->input('question');
        $jobId = $request->input('job_id');

        // استدعاء خدمة المرشد وبناء السياق
        $response = $this->aiMentorService->askMentor($user, $jobId, $question);

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }
}
