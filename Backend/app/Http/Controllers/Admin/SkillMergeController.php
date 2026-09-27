<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SkillMergeRequest;
use App\Http\Resources\SkillResource;
use App\Models\Skill;
use App\Services\SkillTaxonomyService;
use Illuminate\Http\JsonResponse;

class SkillMergeController extends Controller
{
    public function __invoke(SkillMergeRequest $request, SkillTaxonomyService $taxonomy): JsonResponse
    {
        $target = Skill::query()->whereKey($request->validated('target_skill_id'))->firstOrFail();
        $result = $taxonomy->mergeSkill($request->sourceSkill(), $target);

        return response()->json([
            'data' => (new SkillResource($result['skill']->load('skillCategory')))->resolve($request),
            'meta' => $result['meta'],
        ]);
    }
}
