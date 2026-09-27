<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SkillAliasSaveRequest;
use App\Http\Requests\Admin\SkillTaxonomyRequest;
use App\Http\Resources\SkillAliasResource;
use App\Services\SkillTaxonomyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SkillAliasController extends Controller
{
    public function index(SkillTaxonomyRequest $request): JsonResponse
    {
        $aliases = $request->skill()->aliases()->orderBy('normalized_alias')->orderBy('id')->get();

        return response()->json(['data' => SkillAliasResource::collection($aliases)->resolve($request)]);
    }

    public function store(SkillAliasSaveRequest $request, SkillTaxonomyService $taxonomy): JsonResponse
    {
        $alias = $taxonomy->saveAlias($request->skill(), null, $request->validated());

        return response()->json(['data' => (new SkillAliasResource($alias))->resolve($request)], 201);
    }

    public function update(SkillAliasSaveRequest $request, SkillTaxonomyService $taxonomy): JsonResponse
    {
        $alias = $taxonomy->saveAlias($request->skill(), $request->skillAlias(), $request->validated());

        return response()->json(['data' => (new SkillAliasResource($alias))->resolve($request)]);
    }

    public function destroy(SkillTaxonomyRequest $request): Response
    {
        $request->skillAlias()->delete();

        return response()->noContent();
    }
}
