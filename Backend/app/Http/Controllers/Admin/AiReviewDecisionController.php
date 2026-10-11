<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveAiReviewRequest;
use App\Http\Requests\Admin\CorrectAiReviewRequest;
use App\Http\Requests\Admin\RejectAiReviewRequest;
use App\Http\Resources\AiReviewItemResource;
use App\Models\AiReviewItem;
use App\Models\User;
use App\Services\AiReviewDecisionService;
use Illuminate\Http\JsonResponse;

class AiReviewDecisionController extends Controller
{
    public function approve(ApproveAiReviewRequest $request, AiReviewItem $aiReviewItem, AiReviewDecisionService $service): JsonResponse
    {
        /** @var User $reviewer */
        $reviewer = $request->user('api');
        $item = $service->approve($aiReviewItem, $reviewer, $request->validated());

        return response()->json(['data' => (new AiReviewItemResource($item))->resolve($request)]);
    }

    public function correct(CorrectAiReviewRequest $request, AiReviewItem $aiReviewItem, AiReviewDecisionService $service): JsonResponse
    {
        /** @var User $reviewer */
        $reviewer = $request->user('api');
        $item = $service->correct($aiReviewItem, $reviewer, $request->validated());

        return response()->json(['data' => (new AiReviewItemResource($item))->resolve($request)]);
    }

    public function reject(RejectAiReviewRequest $request, AiReviewItem $aiReviewItem, AiReviewDecisionService $service): JsonResponse
    {
        /** @var User $reviewer */
        $reviewer = $request->user('api');
        $item = $service->reject($aiReviewItem, $reviewer, $request->validated());

        return response()->json(['data' => (new AiReviewItemResource($item))->resolve($request)]);
    }
}
