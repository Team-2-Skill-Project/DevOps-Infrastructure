<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\JobPost;
use App\Services\Ai\JobMatchingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobMatchController extends Controller
{
    protected JobMatchingService $jobMatchingService;

    public function __construct(JobMatchingService $jobMatchingService)
    {
        $this->jobMatchingService = $jobMatchingService;
    }

    /**
     * عرض تقرير المطابقة وتحليل الفجوات لوظيفة محددة
     */
    public function getMatchReport(Request $request, JobPost $job): JsonResponse
    {
        $user = $request->user();
        $profile = $user->candidateProfile;

        if (! $profile) {
            return response()->json([
                'success' => false,
                'message' => 'يجب إكمال الملف المهني أولاً.',
            ], 422);
        }

        // حساب وتخزين نتيجة المطابقة عبر خدمة الـ AI
        $matchResult = $this->jobMatchingService->calculateAndPersistMatch($profile, $job);

        return response()->json([
            'success' => true,
            'data' => $matchResult,
        ]);
    }
}
