<?php

namespace App\Http\Requests\Admin;

use App\Enums\AuditAction;
use App\Enums\AuditEntityType;
use App\Enums\AuditSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuditLogListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'actor_id' => ['sometimes', 'integer', 'min:1'],
            'action' => ['sometimes', Rule::in(AuditAction::values())],
            'entity_type' => ['sometimes', Rule::in(AuditEntityType::values())],
            'entity_id' => ['sometimes', 'integer', 'min:1'],
            'source' => ['sometimes', Rule::in(AuditSource::values())],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'search' => ['sometimes', 'string', 'max:100'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return trans('audit.attributes');
    }
}
