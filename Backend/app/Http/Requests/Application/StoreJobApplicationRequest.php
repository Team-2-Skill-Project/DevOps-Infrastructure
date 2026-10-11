<?php

namespace App\Http\Requests\Application;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->candidateProfile !== null;
    }

    /** @return array<string, array<int, string|Rule|\Closure>> */
    public function rules(): array
    {
        $candidateProfileId = optional($this->user()->candidateProfile)->id;

        return [
            'job_id' => [
                'required',
                'exists:job_posts,id',
                Rule::unique('applications', 'job_id')->where(function ($query) use ($candidateProfileId) {
                    return $query->where('candidate_profile_id', $candidateProfileId);
                }),
            ],
            'cover_letter' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'job_id.unique' => __('application.validation.already_applied'),
            'job_id.exists' => __('application.validation.job_not_found'),
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return trans('application.attributes');
    }
}
