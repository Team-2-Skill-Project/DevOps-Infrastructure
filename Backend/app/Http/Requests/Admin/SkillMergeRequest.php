<?php

namespace App\Http\Requests\Admin;

use App\Models\Skill;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class SkillMergeRequest extends FormRequest
{
    private Skill $source;

    public function authorize(): bool
    {
        $source = Skill::query()->whereKey($this->route('sourceSkill'))->first();

        if (! $source) {
            throw new HttpResponseException(response()->json(['message' => __('skill.not_found')], 404));
        }

        $this->source = $source;

        return true;
    }

    /** @return array<string, list<ValidationRule|string>> */
    public function rules(): array
    {
        return [
            'target_skill_id' => ['required', 'integer', Rule::exists(Skill::class, 'id'), Rule::notIn([$this->source->id])],
        ];
    }

    public function sourceSkill(): Skill
    {
        return $this->source;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'target_skill_id.not_in' => __('skill.validation.different_target'),
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return trans('skill.attributes');
    }
}
