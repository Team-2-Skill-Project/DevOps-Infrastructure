<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

final readonly class JobExtractionResult
{
    /** @var array<string, mixed> */
    public array $data;

    /** @param array<string, mixed> $data */
    public function __construct(array $data)
    {
        $rules = [];
        foreach (['title', 'company_name', 'description', 'employment_type', 'job_type', 'work_mode', 'experience_level', 'country', 'state', 'city'] as $field) {
            $rules[$field] = ['sometimes', 'nullable', 'string'];
        }
        foreach (['application_url', 'external_url'] as $field) {
            $rules[$field] = ['sometimes', 'nullable', 'url:http,https', 'max:2048'];
        }
        foreach (['published_at', 'expires_at'] as $field) {
            $rules[$field] = ['sometimes', 'nullable', 'date'];
        }
        foreach (['min_years_experience', 'max_years_experience'] as $field) {
            $rules[$field] = ['sometimes', 'nullable', 'numeric'];
        }
        foreach (['skills', 'responsibilities'] as $field) {
            $rules[$field] = ['sometimes', 'nullable', 'array'];
            $rules[$field.'.*'] = ['string'];
        }
        $this->data = Validator::make($data, $rules)->validate();
    }
}
