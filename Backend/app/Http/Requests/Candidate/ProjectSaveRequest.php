<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class ProjectSaveRequest extends ProjectRequest
{
    /** @return array<string, list<ValidationRule|string>> */
    public function rules(): array
    {
        return [
            'name' => $this->isMethod('POST')
                ? ['required', 'string', 'max:255']
                : ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'technologies' => ['sometimes', 'nullable', 'array', 'list'],
            'technologies.*' => ['required', 'string', 'max:255'],
            'project_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'github_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date'],
            'source' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    /** @return list<\Closure(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $project = $this->isMethod('PATCH') ? $this->project() : null;
            $startDate = $this->has('start_date') ? $this->date('start_date') : $project?->start_date;
            $endDate = $this->has('end_date') ? $this->date('end_date') : $project?->end_date;

            if ($startDate && $endDate && Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
                $validator->errors()->add('end_date', __('candidate_profile.validation.end_date_before_start'));
            }
        }];
    }
}
