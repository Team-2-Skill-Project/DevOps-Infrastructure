<?php

namespace App\Http\Requests\Candidate;

use App\Models\CandidateProfile;
use App\Models\Experience;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExperienceRequest extends FormRequest
{
    private CandidateProfile $candidateProfile;

    private ?Experience $ownedExperience = null;

    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user('api');
        $profile = $user->candidateProfile()->first();

        if (! $profile) {
            throw new HttpResponseException(response()->json(['message' => __('candidate_profile.not_found')], 404));
        }

        $this->candidateProfile = $profile;

        if ($this->route('experience') !== null) {
            $this->ownedExperience = $profile->experiences()->whereKey($this->route('experience'))->first();
            $this->experience();
        }

        return true;
    }

    public function profile(): CandidateProfile
    {
        return $this->candidateProfile;
    }

    public function experience(): Experience
    {
        if (! $this->ownedExperience) {
            throw new HttpResponseException(response()->json(['message' => __('candidate_profile.experience.not_found')], 404));
        }

        return $this->ownedExperience;
    }
}
