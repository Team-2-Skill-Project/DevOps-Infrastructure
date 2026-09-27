<?php

namespace App\Http\Requests\Admin;

use App\Models\SkillCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SkillSaveRequest extends SkillTaxonomyRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('name'))) {
            $this->merge(['name' => Str::trim($this->input('name'))]);
        }
    }

    /** @return array<string, list<ValidationRule|string>> */
    public function rules(): array
    {
        return [
            'name' => $this->isMethod('POST') ? ['required', 'string', 'max:255'] : ['sometimes', 'required', 'string', 'max:255'],
            'skill_category_id' => ['sometimes', 'nullable', 'integer', Rule::exists(SkillCategory::class, 'id')],
            'category' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
