<?php

namespace App\Http\Requests\Admin;

use App\Models\Skill;
use App\Models\SkillAlias;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SkillTaxonomyRequest extends FormRequest
{
    private ?Skill $parentSkill = null;

    private ?SkillAlias $ownedAlias = null;

    public function authorize(): bool
    {
        if ($this->route('skill') !== null) {
            $this->parentSkill = Skill::query()->whereKey($this->route('skill'))->first();
            $this->skill();
        }

        if ($this->route('alias') !== null) {
            $this->ownedAlias = $this->skill()->aliases()->whereKey($this->route('alias'))->first();
            $this->skillAlias();
        }

        return true;
    }

    public function skill(): Skill
    {
        if (! $this->parentSkill) {
            throw new HttpResponseException(response()->json(['message' => __('skill.not_found')], 404));
        }

        return $this->parentSkill;
    }

    public function skillAlias(): SkillAlias
    {
        if (! $this->ownedAlias) {
            throw new HttpResponseException(response()->json(['message' => __('skill.alias_not_found')], 404));
        }

        return $this->ownedAlias;
    }
}
