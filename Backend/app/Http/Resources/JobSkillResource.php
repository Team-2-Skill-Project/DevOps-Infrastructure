<?php

namespace App\Http\Resources;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Skill */
class JobSkillResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $pivot = $this->resource->getRelationValue('pivot');

        return [
            ...$this->only(['id', 'name']),
            'importance' => $pivot instanceof Pivot ? $pivot->getAttribute('importance') : null,
            'required_level' => $pivot instanceof Pivot ? $pivot->getAttribute('required_level') : null,
        ];
    }
}
