<?php

namespace App\Http\Controllers;

use App\Http\Requests\Roadmap\CompleteRoadmapTaskRequest;
use App\Http\Resources\RoadmapResource;
use App\Services\RoadmapProgressService;
use Illuminate\Http\JsonResponse;

class RoadmapTaskController extends Controller
{
    public function complete(CompleteRoadmapTaskRequest $request, RoadmapProgressService $service): JsonResponse
    {
        $roadmap = $service->completeTask(
            $request->profile(),
            (int) $request->roadmapTask()->getKey(),
            $request->safe()->only(['evidence', 'notes']),
        );

        return (new RoadmapResource($roadmap))
            ->additional(['status' => 'success', 'message' => __('roadmap.task_completed')])
            ->response();
    }
}
