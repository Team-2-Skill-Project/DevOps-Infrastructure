<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\CareerPreferenceUpdateRequest;
use App\Http\Resources\CareerPreferenceResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CareerPreferenceController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');
        $profile = $user->candidateProfile()->first();

        if (! $profile) {
            return response()->json(['message' => __('candidate_profile.not_found')], 404);
        }

        $preference = $profile->careerPreference;

        return response()->json([
            'data' => $preference ? (new CareerPreferenceResource($preference))->resolve($request) : null,
        ]);
    }

    public function update(CareerPreferenceUpdateRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');
        $profile = $user->candidateProfile()->first();

        if (! $profile) {
            return response()->json(['message' => __('candidate_profile.not_found')], 404);
        }

        $preference = $profile->careerPreference()->updateOrCreate([], $request->validated());

        return response()->json([
            'data' => (new CareerPreferenceResource($preference->refresh()))->resolve($request),
        ]);
    }
}
