<?php

namespace App\Http\Requests\Admin;

use App\Models\IngestionRun;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IngestionRunListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::in(IngestionRun::STATUSES)],
            'trigger_type' => ['sometimes', Rule::in(IngestionRun::TRIGGER_TYPES)],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
