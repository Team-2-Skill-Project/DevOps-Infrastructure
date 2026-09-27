<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class SkillAliasSaveRequest extends SkillTaxonomyRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('alias'))) {
            $this->merge(['alias' => Str::trim($this->input('alias'))]);
        }
    }

    /** @return array<string, list<ValidationRule|string>> */
    public function rules(): array
    {
        return ['alias' => $this->isMethod('POST') ? ['required', 'string', 'max:255'] : ['sometimes', 'required', 'string', 'max:255']];
    }
}
