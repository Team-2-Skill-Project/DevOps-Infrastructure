<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\ProfileUpdateRequest;
use App\Http\Resources\CandidateProfileResource;
use App\Http\Resources\UserResource;
use App\Models\CandidateProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        return $this->profileResponse($request, $user, $this->loadProfile($user));
    }

    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        $profile = $user->candidateProfile()->first();

        if ($profile) {
            $profile->update($request->validated());
        } else {
            $user->candidateProfile()->create($request->validated());
        }

        return $this->profileResponse($request, $user, $this->loadProfile($user));
    }

    private function loadProfile(User $user): ?CandidateProfile
    {
        return $user->candidateProfile()->with([
            'careerPreference',
            'educations',
            'experiences',
            'projects',
            'certificates',
            'languages',
            'candidateSkills.skill',
            'cvDocuments:id,candidate_profile_id,original_filename,mime_type,file_size,version,is_current,status,processed_at,created_at,updated_at',
            'cvDocuments.extractions:id,cv_document_id,attempt_number,status,confidence_score,started_at,completed_at',
        ])->first();
    }

    private function profileResponse(Request $request, User $user, ?CandidateProfile $profile): JsonResponse
    {
        return response()->json([
            'data' => [
                'profile_exists' => $profile !== null,
                'user' => (new UserResource($user))->resolve($request),
                'profile' => $profile ? (new CandidateProfileResource($profile))->resolve($request) : null,
            ],
        ]);
    }
}
