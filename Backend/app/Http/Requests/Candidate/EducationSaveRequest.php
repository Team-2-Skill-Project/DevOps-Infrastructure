<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class EducationSaveRequest extends EducationRequest
{
    /** @return array<string, list<ValidationRule|string>> */
    public function rules(): array
    {
        return [
            'education_level' => ['sometimes', 'nullable', 'string', 'max:100'],
            'institution' => $this->isMethod('POST')
                ? ['required', 'string', 'max:255']
                : ['sometimes', 'required', 'string', 'max:255'],
            'field_of_study' => ['sometimes', 'nullable', 'string', 'max:255'],
            'degree' => ['sometimes', 'nullable', 'string', 'max:255'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date'],
            'is_current' => ['sometimes', 'boolean'],
            'grade' => ['sometimes', 'nullable', 'string', 'max:100'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'source' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }

    /** @return list<\Closure(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $education = $this->isMethod('PATCH') ? $this->education() : null;
            $startDate = $this->has('start_date') ? $this->date('start_date') : $education?->start_date;
            $endDate = $this->has('end_date') ? $this->date('end_date') : $education?->end_date;
            $isCurrent = $this->has('is_current') ? $this->boolean('is_current') : ($education->is_current ?? false);

            if ($startDate && $endDate && Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
                $validator->errors()->add('end_date', __('candidate_profile.validation.end_date_before_start'));
            }

            if ($isCurrent && $endDate) {
                $validator->errors()->add('end_date', __('candidate_profile.validation.current_education_end_date'));
            }
        }];
    }
}
