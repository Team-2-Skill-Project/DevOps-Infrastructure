<?php

namespace App\Http\Resources;

use App\Models\CandidateSkill;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CandidateSkill */
class CandidateSkillResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            ...$this->only(['id', 'skill_id', 'source', 'proficiency_level']),
            'skill' => $this->skill?->only(['id', 'name', 'normalized_name', 'category']),
        ];
    }
}
