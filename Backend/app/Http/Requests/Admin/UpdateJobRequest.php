<?php

namespace App\Http\Requests\Admin;

use App\Models\Company;
use App\Models\JobPost;
use App\Models\Skill;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateJobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['sometimes', 'integer', Rule::exists(Company::class, 'id')],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'job_type' => ['sometimes', 'in:'.JobPost::TYPE_JOB.','.JobPost::TYPE_INTERNSHIP],
            'employment_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'work_mode' => ['sometimes', 'nullable', 'string', 'max:255'],
            'experience_level' => ['sometimes', 'nullable', 'string', 'max:255'],
            'country' => ['sometimes', 'nullable', 'string', 'max:255'],
            'state' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'min_years_experience' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'max_years_experience' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'responsibilities' => ['sometimes', 'nullable', 'array'],
            'canonical_role' => ['sometimes', 'nullable', 'string', 'max:150'],
            'salary_min' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'salary_max' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'salary_currency' => ['sometimes', 'nullable', 'string', 'size:3'],
            'published_at' => ['sometimes', 'nullable', 'date'],
            'expires_at' => ['sometimes', 'nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
            'application_method' => ['sometimes', 'in:'.JobPost::APPLICATION_INTERNAL.','.JobPost::APPLICATION_EXTERNAL],
            'application_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'required_skills' => ['sometimes', 'array'],
            'required_skills.*.skill_id' => ['required', 'integer', Rule::exists(Skill::class, 'id')],
            'required_skills.*.importance' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:5'],
            'required_skills.*.required_level' => ['sometimes', 'nullable', 'string', 'max:50'],
            'preferred_skills' => ['sometimes', 'array'],
            'preferred_skills.*.skill_id' => ['required', 'integer', Rule::exists(Skill::class, 'id')],
            'preferred_skills.*.importance' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:5'],
            'preferred_skills.*.required_level' => ['sometimes', 'nullable', 'string', 'max:50'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $job = JobPost::query()->findOrFail((int) $this->route('jobPost'));
            $this->validateCrossFields($validator, $job);
        });
    }

    private function validateCrossFields(Validator $validator, ?JobPost $job): void
    {
        $min = $this->input('min_years_experience', $job?->min_years_experience);
        $max = $this->input('max_years_experience', $job?->max_years_experience);
        if ($min !== null && $max !== null && (int) $max < (int) $min) {
            $validator->errors()->add('max_years_experience', 'The maximum experience must be at least the minimum experience.');
        }

        $publishedAt = $this->input('published_at', $job?->getRawOriginal('published_at'));
        $expiresAt = $this->input('expires_at', $job?->getRawOriginal('expires_at'));
        if ($publishedAt && $expiresAt && strtotime($expiresAt) <= strtotime($publishedAt)) {
            $validator->errors()->add('expires_at', 'The expiration date must be after the publication date.');
        }
        if ($expiresAt && strtotime($expiresAt) <= time()) {
            $validator->errors()->add('expires_at', 'The expiration date must be in the future.');
        }

        $method = $this->input('application_method', $job ? $job->application_method : JobPost::APPLICATION_INTERNAL);
        $url = $this->exists('application_url') ? $this->input('application_url') : $job?->application_url;
        if ($method === JobPost::APPLICATION_EXTERNAL && ! $url) {
            $validator->errors()->add('application_url', 'An application URL is required for external applications.');
        }
        if ($method === JobPost::APPLICATION_INTERNAL && $this->filled('application_url')) {
            $validator->errors()->add('application_url', 'An application URL is not allowed for internal applications.');
        }

        /** @var list<array{skill_id?: int}> $requiredInput */
        $requiredInput = $this->input('required_skills', []);
        /** @var list<array{skill_id?: int}> $preferredInput */
        $preferredInput = $this->input('preferred_skills', []);
        $required = collect($requiredInput)->pluck('skill_id');
        $preferred = collect($preferredInput)->pluck('skill_id');
        if ($required->duplicates()->isNotEmpty()) {
            $validator->errors()->add('required_skills', 'Required skills cannot contain duplicates.');
        }
        if ($preferred->duplicates()->isNotEmpty()) {
            $validator->errors()->add('preferred_skills', 'Preferred skills cannot contain duplicates.');
        }
        if ($required->intersect($preferred)->isNotEmpty()) {
            $validator->errors()->add('preferred_skills', 'A skill cannot be both required and preferred.');
        }
    }
}
