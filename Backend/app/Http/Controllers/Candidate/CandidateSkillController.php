<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\CandidateSkillRequest;
use App\Http\Requests\Candidate\CandidateSkillStoreRequest;
use App\Http\Resources\CandidateSkillResource;
use App\Models\CandidateSkill;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CandidateSkillController extends Controller
{
    public function index(CandidateSkillRequest $request): JsonResponse
    {
        $skills = $request->profile()->candidateSkills()->with('skill')->orderByDesc('id')->get();

        return response()->json(['data' => CandidateSkillResource::collection($skills)->resolve($request)]);
    }

    public function store(CandidateSkillStoreRequest $request): JsonResponse
    {
        $candidateSkill = DB::transaction(function () use ($request): CandidateSkill {
            $data = $request->validated();
            $skill = Skill::query()->firstOrCreate(
                ['normalized_name' => Str::lower($data['name'])],
                ['name' => $data['name']],
            );

            $candidateSkill = $request->profile()->candidateSkills()->firstOrCreate(
                ['skill_id' => $skill->id],
                ['source' => CandidateSkill::SOURCE_MANUAL, 'proficiency_level' => $data['proficiency_level'] ?? null],
            );

            if (! $candidateSkill->wasRecentlyCreated) {
                throw ValidationException::withMessages(['name' => __('candidate_profile.skill.already_attached')]);
            }

            return $candidateSkill;
        });

        return response()->json([
            'data' => (new CandidateSkillResource($candidateSkill->load('skill')))->resolve($request),
        ], 201);
    }

    public function destroy(CandidateSkillRequest $request): Response
    {
        $request->candidateSkill()->delete();

        return response()->noContent();
    }
}
