<?php

namespace App\Http\Requests\Roadmap;

use App\Models\CandidateProfile;
use App\Models\Roadmap;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RoadmapRequest extends FormRequest
{
    private CandidateProfile $candidateProfile;

    private ?Roadmap $ownedRoadmap = null;

    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user('api');
        $profile = $user->candidateProfile()->first();

        if ($profile === null) {
            throw new HttpResponseException(response()->json(['message' => __('candidate_profile.not_found')], 404));
        }

        $this->candidateProfile = $profile;
        if ($this->route('roadmap') !== null) {
            $this->ownedRoadmap = $profile->roadmaps()->whereKey($this->route('roadmap'))->first();
            $this->roadmap();
        }

        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [];
    }

    public function profile(): CandidateProfile
    {
        return $this->candidateProfile;
    }

    public function roadmap(): Roadmap
    {
        if ($this->ownedRoadmap === null) {
            throw new HttpResponseException(response()->json(['message' => __('roadmap.not_found')], 404));
        }

        return $this->ownedRoadmap;
    }
}
