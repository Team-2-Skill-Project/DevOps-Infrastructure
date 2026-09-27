<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<ValidationRule|string>> */
    public function rules(): array
    {
        return [
            'date_of_birth' => ['sometimes', 'nullable', 'date', 'before:today'],
            'gender' => ['sometimes', 'nullable', 'string', 'max:255'],
            'job_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'country' => ['sometimes', 'nullable', 'string', 'max:255'],
            'state' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'military_status' => ['sometimes', 'nullable', 'string', 'max:255'],
            'github_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'linkedin_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'professional_summary' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}
