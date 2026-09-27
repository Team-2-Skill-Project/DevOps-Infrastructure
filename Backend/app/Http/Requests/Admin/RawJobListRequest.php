<?php

namespace App\Http\Requests\Admin;

use App\Models\RawJob;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RawJobListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'ingestion_run_id' => ['sometimes', 'integer', 'min:1'],
            'extraction_status' => ['sometimes', Rule::in(RawJob::STATUSES)],
            'deduplication_status' => ['sometimes', Rule::in(RawJob::DEDUPLICATION_STATUSES)],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
