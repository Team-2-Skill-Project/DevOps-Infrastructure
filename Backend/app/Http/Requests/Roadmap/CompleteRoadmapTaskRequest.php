<?php

namespace App\Http\Requests\Roadmap;

use App\Models\CandidateProfile;
use App\Models\RoadmapTask;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CompleteRoadmapTaskRequest extends FormRequest
{
    private CandidateProfile $candidateProfile;

    private RoadmapTask $ownedTask;

    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user('api');
        $profile = $user->candidateProfile()->first();

        if ($profile === null) {
            throw new HttpResponseException(response()->json(['message' => __('candidate_profile.not_found')], 404));
        }

        $task = RoadmapTask::query()
            ->whereKey($this->route('roadmapTask'))
            ->whereHas('roadmap', fn ($query) => $query->where('candidate_profile_id', $profile->id))
            ->first();

        if ($task === null) {
            throw new HttpResponseException(response()->json(['message' => __('roadmap.task_not_found')], 404));
        }

        $this->candidateProfile = $profile;
        $this->ownedTask = $task;

        return true;
    }

    /** @return array<string, list<ValidationRule|string>> */
    public function rules(): array
    {
        return [
            'evidence' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'progress' => ['prohibited'],
            'status' => ['prohibited'],
            'completed_at' => ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return trans('roadmap.attributes');
    }

    public function profile(): CandidateProfile
    {
        return $this->candidateProfile;
    }

    public function roadmapTask(): RoadmapTask
    {
        return $this->ownedTask;
    }
}
