<?php

namespace App\Http\Requests\Admin;

use App\Models\JobSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class JobSourceSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['name', 'slug'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => Str::trim($this->input($field))]);
            }
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $required = $this->isMethod('POST') ? 'required' : 'sometimes';
        $source = $this->route('jobSource');

        return [
            'name' => [$required, 'required', 'string', 'max:255'],
            'slug' => [$required, 'required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('job_sources')->ignore($source instanceof JobSource ? $source->id : null)],
            'source_type' => [$required, 'required', Rule::in(JobSource::SOURCE_TYPES)],
            'collection_method' => [$required, 'required', Rule::in(JobSource::COLLECTION_METHODS)],
            'base_url' => ['sometimes', 'nullable', 'url:http,https', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
            'schedule_enabled' => ['sometimes', 'boolean'],
            'schedule_expression' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'created_by' => ['prohibited'],
            'id' => ['prohibited'],
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited'],
        ];
    }
}
