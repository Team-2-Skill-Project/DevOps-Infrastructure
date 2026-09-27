<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class ExperienceSaveRequest extends ExperienceRequest
{
    /** @return array<string, list<ValidationRule|string>> */
    public function rules(): array
    {
        $requiredString = $this->isMethod('POST')
            ? ['required', 'string', 'max:255']
            : ['sometimes', 'required', 'string', 'max:255'];

        return [
            'technologies' => ['sometimes', 'nullable', 'array', 'list'],
            'technologies.*' => ['required', 'string', 'max:255'],
            'job_title' => $requiredString,
            'company_name' => $requiredString,
            'employment_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'country' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date'],
            'is_current' => ['sometimes', 'boolean'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
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

            $experience = $this->isMethod('PATCH') ? $this->experience() : null;
            $startDate = $this->has('start_date') ? $this->date('start_date') : $experience?->start_date;
            $endDate = $this->has('end_date') ? $this->date('end_date') : $experience?->end_date;
            $isCurrent = $this->has('is_current') ? $this->boolean('is_current') : ($experience->is_current ?? false);

            if ($startDate && $endDate && Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
                $validator->errors()->add('end_date', __('candidate_profile.validation.end_date_before_start'));
            }

            if ($isCurrent && $endDate) {
                $validator->errors()->add('end_date', __('candidate_profile.validation.current_experience_end_date'));
            }
        }];
    }
}
