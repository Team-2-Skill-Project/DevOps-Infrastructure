<?php

namespace App\Http\Requests\Candidate;

use App\Models\CandidateProfile;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProjectRequest extends FormRequest
{
    private CandidateProfile $candidateProfile;

    private ?Project $ownedProject = null;

    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user('api');
        $profile = $user->candidateProfile()->first();

        if (! $profile) {
            throw new HttpResponseException(response()->json(['message' => __('candidate_profile.not_found')], 404));
        }

        $this->candidateProfile = $profile;

        if ($this->route('project') !== null) {
            $this->ownedProject = $profile->projects()->whereKey($this->route('project'))->first();
            $this->project();
        }

        return true;
    }

    public function profile(): CandidateProfile
    {
        return $this->candidateProfile;
    }

    public function project(): Project
    {
        if (! $this->ownedProject) {
            throw new HttpResponseException(response()->json(['message' => __('candidate_profile.project.not_found')], 404));
        }

        return $this->ownedProject;
    }
}
