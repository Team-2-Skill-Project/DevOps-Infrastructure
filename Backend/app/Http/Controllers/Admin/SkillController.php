<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SkillSaveRequest;
use App\Http\Resources\SkillResource;
use App\Services\SkillTaxonomyService;
use Illuminate\Http\JsonResponse;

class SkillController extends Controller
{
    public function store(SkillSaveRequest $request, SkillTaxonomyService $taxonomy): JsonResponse
    {
        $skill = $taxonomy->saveSkill(null, $request->validated());

        return response()->json(['data' => (new SkillResource($skill->refresh()->load('skillCategory')))->resolve($request)], 201);
    }

    public function update(SkillSaveRequest $request, SkillTaxonomyService $taxonomy): JsonResponse
    {
        $skill = $taxonomy->saveSkill($request->skill(), $request->validated());

        return response()->json(['data' => (new SkillResource($skill->refresh()->load('skillCategory')))->resolve($request)]);
    }
}
