<?php

namespace App\Http\Requests\Candidate;

use App\Models\CandidateProfile;
use App\Models\Education;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class EducationRequest extends FormRequest
{
    private CandidateProfile $candidateProfile;

    private ?Education $ownedEducation = null;

    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user('api');
        $profile = $user->candidateProfile()->first();

        if (! $profile) {
            throw new HttpResponseException(response()->json(['message' => __('candidate_profile.not_found')], 404));
        }

        $this->candidateProfile = $profile;

        if ($this->route('education') !== null) {
            $this->ownedEducation = $profile->educations()->whereKey($this->route('education'))->first();
            $this->education();
        }

        return true;
    }

    public function profile(): CandidateProfile
    {
        return $this->candidateProfile;
    }

    public function education(): Education
    {
        if (! $this->ownedEducation) {
            throw new HttpResponseException(response()->json(['message' => __('candidate_profile.education.not_found')], 404));
        }

        return $this->ownedEducation;
    }
}
