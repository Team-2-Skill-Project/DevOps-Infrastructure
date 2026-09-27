<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\ExperienceRequest;
use App\Http\Requests\Candidate\ExperienceSaveRequest;
use App\Http\Resources\ExperienceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ExperienceController extends Controller
{
    public function index(ExperienceRequest $request): JsonResponse
    {
        $experiences = $request->profile()->experiences()->orderByDesc('start_date')->orderByDesc('id')->get();

        return response()->json(['data' => ExperienceResource::collection($experiences)->resolve($request)]);
    }

    public function store(ExperienceSaveRequest $request): JsonResponse
    {
        $experience = $request->profile()->experiences()->create($request->validated());

        return response()->json(['data' => (new ExperienceResource($experience->refresh()))->resolve($request)], 201);
    }

    public function show(ExperienceRequest $request): JsonResponse
    {
        return response()->json(['data' => (new ExperienceResource($request->experience()))->resolve($request)]);
    }

    public function update(ExperienceSaveRequest $request): JsonResponse
    {
        $experience = $request->experience();
        $experience->update($request->validated());

        return response()->json(['data' => (new ExperienceResource($experience->refresh()))->resolve($request)]);
    }

    public function destroy(ExperienceRequest $request): Response
    {
        $request->experience()->delete();

        return response()->noContent();
    }
}
