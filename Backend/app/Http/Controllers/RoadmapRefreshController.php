<?php

namespace App\Http\Controllers;

use App\Http\Requests\Roadmap\RefreshRoadmapRequest;
use App\Http\Resources\RoadmapResource;
use App\Services\RoadmapRefreshService;
use Illuminate\Http\JsonResponse;

class RoadmapRefreshController extends Controller
{
    public function __invoke(RefreshRoadmapRequest $request, RoadmapRefreshService $service): JsonResponse
    {
        $data = $request->validated();
        $result = $service->refresh(
            $request->profile(),
            $request->roadmap(),
            $data['target_type'] ?? null,
            $data['target_role'] ?? null,
            $data['target_job_id'] ?? null,
        );

        return (new RoadmapResource($result['roadmap']))
            ->additional([
                'status' => 'success',
                'message' => __($result['refreshed'] ? 'roadmap.refreshed' : 'roadmap.refresh_not_required'),
                'meta' => ['refreshed' => $result['refreshed'], 'reasons' => $result['reasons']],
            ])->response()
            ->setStatusCode(200);
    }
}
