<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\ProjectRequest;
use App\Http\Requests\Candidate\ProjectSaveRequest;
use App\Http\Resources\ProjectResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    public function index(ProjectRequest $request): JsonResponse
    {
        $projects = $request->profile()->projects()->orderByDesc('start_date')->orderByDesc('id')->get();

        return response()->json(['data' => ProjectResource::collection($projects)->resolve($request)]);
    }

    public function store(ProjectSaveRequest $request): JsonResponse
    {
        $project = $request->profile()->projects()->create($request->validated());

        return response()->json(['data' => (new ProjectResource($project->refresh()))->resolve($request)], 201);
    }

    public function show(ProjectRequest $request): JsonResponse
    {
        return response()->json(['data' => (new ProjectResource($request->project()))->resolve($request)]);
    }

    public function update(ProjectSaveRequest $request): JsonResponse
    {
        $project = $request->project();
        $project->update($request->validated());

        return response()->json(['data' => (new ProjectResource($project->refresh()))->resolve($request)]);
    }

    public function destroy(ProjectRequest $request): Response
    {
        $request->project()->delete();

        return response()->noContent();
    }
}
