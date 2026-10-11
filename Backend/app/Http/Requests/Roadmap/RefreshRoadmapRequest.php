<?php

namespace App\Http\Requests\Roadmap;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class RefreshRoadmapRequest extends RoadmapRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('target_role'))) {
            $this->merge(['target_role' => Str::squish($this->input('target_role'))]);
        }
    }

    /** @return array<string, list<ValidationRule|string>> */
    public function rules(): array
    {
        return [
            'target_type' => ['sometimes', 'required', 'in:role,job'],
            'target_role' => ['required_if:target_type,role', 'prohibited_unless:target_type,role', 'string', 'max:150'],
            'target_job_id' => ['required_if:target_type,job', 'prohibited_unless:target_type,job', 'integer'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return trans('roadmap.attributes');
    }
}
