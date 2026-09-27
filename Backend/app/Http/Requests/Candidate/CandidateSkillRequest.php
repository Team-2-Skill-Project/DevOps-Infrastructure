<?php

namespace App\Http\Requests\Candidate;

use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CandidateSkillRequest extends FormRequest
{
    private CandidateProfile $candidateProfile;

    private ?CandidateSkill $ownedCandidateSkill = null;

    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user('api');
        $profile = $user->candidateProfile()->first();

        if (! $profile) {
            throw new HttpResponseException(response()->json(['message' => __('candidate_profile.not_found')], 404));
        }

        $this->candidateProfile = $profile;

        if ($this->route('candidateSkill') !== null) {
            $this->ownedCandidateSkill = $profile->candidateSkills()->whereKey($this->route('candidateSkill'))->first();
            $this->candidateSkill();
        }

        return true;
    }

    public function profile(): CandidateProfile
    {
        return $this->candidateProfile;
    }

    public function candidateSkill(): CandidateSkill
    {
        if (! $this->ownedCandidateSkill) {
            throw new HttpResponseException(response()->json(['message' => __('candidate_profile.skill.not_found')], 404));
        }

        return $this->ownedCandidateSkill;
    }
}
