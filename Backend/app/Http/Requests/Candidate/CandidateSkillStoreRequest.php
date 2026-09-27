<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class CandidateSkillStoreRequest extends CandidateSkillRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('name'))) {
            $this->merge(['name' => Str::trim($this->input('name'))]);
        }
    }

    /** @return array<string, list<ValidationRule|string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'proficiency_level' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
