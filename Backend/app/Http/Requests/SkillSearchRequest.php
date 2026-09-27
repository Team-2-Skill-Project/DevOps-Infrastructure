<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class SkillSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('q'))) {
            $this->merge(['q' => Str::trim($this->input('q'))]);
        }
    }

    /** @return array<string, list<ValidationRule|string>> */
    public function rules(): array
    {
        return ['q' => ['required', 'string', 'min:1', 'max:255']];
    }
}
