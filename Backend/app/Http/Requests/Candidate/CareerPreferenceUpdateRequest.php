<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CareerPreferenceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<ValidationRule|string>> */
    public function rules(): array
    {
        return [
            'target_roles' => ['sometimes', 'nullable', 'array', 'list'],
            'target_roles.*' => ['required', 'string', 'max:255'],
            'preferred_industries' => ['sometimes', 'nullable', 'array', 'list'],
            'preferred_industries.*' => ['required', 'string', 'max:255'],
            'target_role' => ['sometimes', 'nullable', 'string', 'max:255'],
            'job_type' => ['sometimes', 'nullable', 'string', 'max:100'],
            'work_mode' => ['sometimes', 'nullable', 'string', 'max:100'],
            'preferred_country' => ['sometimes', 'nullable', 'string', 'max:255'],
            'preferred_city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'experience_level' => ['sometimes', 'nullable', 'string', 'max:100'],
            'career_goal' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'open_to_relocation' => ['sometimes', 'boolean'],
        ];
    }
}
