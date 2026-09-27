<?php

namespace App\Http\Resources;

use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Skill */
class SkillResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            ...$this->only(['id', 'name', 'normalized_name', 'category', 'skill_category_id']),
            'skill_category' => $this->skillCategory?->only(['id', 'name', 'normalized_name', 'is_active']),
        ];
    }
}
