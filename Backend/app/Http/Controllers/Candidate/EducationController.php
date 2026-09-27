<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\EducationRequest;
use App\Http\Requests\Candidate\EducationSaveRequest;
use App\Http\Resources\EducationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class EducationController extends Controller
{
    public function index(EducationRequest $request): JsonResponse
    {
        $educations = $request->profile()->educations()->orderByDesc('start_date')->orderByDesc('id')->get();

        return response()->json(['data' => EducationResource::collection($educations)->resolve($request)]);
    }

    public function store(EducationSaveRequest $request): JsonResponse
    {
        $education = $request->profile()->educations()->create($request->validated());

        return response()->json(['data' => (new EducationResource($education->refresh()))->resolve($request)], 201);
    }

    public function show(EducationRequest $request): JsonResponse
    {
        return response()->json(['data' => (new EducationResource($request->education()))->resolve($request)]);
    }

    public function update(EducationSaveRequest $request): JsonResponse
    {
        $education = $request->education();
        $education->update($request->validated());

        return response()->json(['data' => (new EducationResource($education->refresh()))->resolve($request)]);
    }

    public function destroy(EducationRequest $request): Response
    {
        $request->education()->delete();

        return response()->noContent();
    }
}
