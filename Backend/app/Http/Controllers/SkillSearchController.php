<?php

namespace App\Http\Controllers;

use App\Http\Requests\SkillSearchRequest;
use App\Http\Resources\SkillResource;
use App\Models\Skill;
use App\Services\SkillTaxonomyService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class SkillSearchController extends Controller
{
    public function __invoke(SkillSearchRequest $request, SkillTaxonomyService $taxonomy): JsonResponse
    {
        $query = $taxonomy->normalize($request->validated('q'));
        $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $query);
        $contains = '%'.$escaped.'%';
        $prefix = $escaped.'%';

        $skills = Skill::query()->with('skillCategory')
            ->where(function (Builder $skills) use ($contains): void {
                $skills->whereRaw("(LOWER(skills.name) LIKE ? ESCAPE '!' OR skills.normalized_name LIKE ? ESCAPE '!')", [$contains, $contains])
                    ->orWhereHas('aliases', function (Builder $aliases) use ($contains): void {
                        $aliases->whereRaw("(LOWER(alias) LIKE ? ESCAPE '!' OR normalized_alias LIKE ? ESCAPE '!')", [$contains, $contains]);
                    });
            })
            ->orderByRaw(
                'CASE WHEN skills.normalized_name = ? THEN 0 '
                .'WHEN EXISTS (SELECT 1 FROM skill_aliases WHERE skill_aliases.skill_id = skills.id AND normalized_alias = ?) THEN 1 '
                ."WHEN (LOWER(skills.name) LIKE ? ESCAPE '!' OR skills.normalized_name LIKE ? ESCAPE '!') "
                .'OR EXISTS (SELECT 1 FROM skill_aliases WHERE skill_aliases.skill_id = skills.id '
                ."AND (LOWER(alias) LIKE ? ESCAPE '!' OR normalized_alias LIKE ? ESCAPE '!')) THEN 2 ELSE 3 END",
                [$query, $query, $prefix, $prefix, $prefix, $prefix],
            )
            ->orderBy('normalized_name')->orderBy('id')->limit(20)->get();

        return response()->json(['data' => SkillResource::collection($skills)->resolve($request)]);
    }
}
