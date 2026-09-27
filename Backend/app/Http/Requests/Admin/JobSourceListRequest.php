<?php

namespace App\Http\Requests\Admin;

use App\Models\JobSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobSourceListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['is_active', 'schedule_enabled'] as $field) {
            if (in_array($this->input($field), ['true', 'false'], true)) {
                $this->merge([$field => $this->input($field) === 'true']);
            }
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'schedule_enabled' => ['sometimes', 'boolean'],
            'source_type' => ['sometimes', Rule::in(JobSource::SOURCE_TYPES)],
            'collection_method' => ['sometimes', Rule::in(JobSource::COLLECTION_METHODS)],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
