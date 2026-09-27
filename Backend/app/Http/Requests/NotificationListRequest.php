<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class NotificationListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (in_array($this->input('unread_only'), ['true', 'false'], true)) {
            $this->merge(['unread_only' => $this->input('unread_only') === 'true']);
        }
    }

    /** @return array<string, list<ValidationRule|string>> */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'unread_only' => ['sometimes', 'nullable', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return trans('notifications.attributes');
    }
}
