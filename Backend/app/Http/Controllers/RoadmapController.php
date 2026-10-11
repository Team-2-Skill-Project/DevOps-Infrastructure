<?php

namespace App\Http\Controllers;

use App\Http\Requests\Roadmap\RoadmapRequest;
use App\Http\Requests\Roadmap\StoreRoadmapRequest;
use App\Http\Resources\RoadmapResource;
use App\Services\RoadmapGenerationService;
use App\Services\RoadmapProgressService;
use Illuminate\Http\JsonResponse;

class RoadmapController extends Controller
{
    public function store(StoreRoadmapRequest $request, RoadmapGenerationService $service): JsonResponse
    {
        $data = $request->validated();
        $roadmap = $service->generate(
            $request->profile(),
            $data['target_type'],
            $data['target_role'] ?? null,
            $data['target_job_id'] ?? null,
        );

        return (new RoadmapResource($roadmap))
            ->additional(['status' => 'success', 'message' => __('roadmap.generated')])
            ->response()
            ->setStatusCode(201);
    }

    public function show(RoadmapRequest $request): RoadmapResource
    {
        return new RoadmapResource(
            $request->roadmap()->load(RoadmapProgressService::hierarchyRelations()),
        );
    }
}
