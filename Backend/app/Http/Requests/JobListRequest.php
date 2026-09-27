<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\JobPost;
use App\Models\Skill;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (in_array($this->input('is_verified_company'), ['true', 'false'], true)) {
            $this->merge(['is_verified_company' => $this->input('is_verified_company') === 'true']);
        }

        foreach (['required_skill_ids', 'preferred_skill_ids'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => [$this->input($field)]]);
            }
        }
    }

    /** @return array<string, list<ValidationRule|string>> */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort' => ['sometimes', 'in:newest,relevance'],
            'company_id' => ['sometimes', 'integer', Rule::exists(Company::class, 'id')],
            'job_type' => ['sometimes', 'in:'.JobPost::TYPE_JOB.','.JobPost::TYPE_INTERNSHIP],
            'work_mode' => ['sometimes', 'string', 'max:255'],
            'employment_type' => ['sometimes', 'string', 'max:255'],
            'experience_level' => ['sometimes', 'string', 'max:255'],
            'country' => ['sometimes', 'string', 'max:255'],
            'state' => ['sometimes', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:255'],
            'source' => ['sometimes', 'string', 'max:255'],
            'application_method' => ['sometimes', 'in:'.JobPost::APPLICATION_INTERNAL.','.JobPost::APPLICATION_EXTERNAL],
            'is_verified_company' => ['sometimes', 'boolean'],
            'required_skill_ids' => ['sometimes', 'array'],
            'required_skill_ids.*' => ['integer', 'distinct', Rule::exists(Skill::class, 'id')],
            'preferred_skill_ids' => ['sometimes', 'array'],
            'preferred_skill_ids.*' => ['integer', 'distinct', Rule::exists(Skill::class, 'id')],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return trans('jobs.attributes');
    }
}
