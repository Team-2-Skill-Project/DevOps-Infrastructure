<?php

namespace App\Http\Resources;

use App\Models\SkillAlias;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SkillAlias */
class SkillAliasResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return $this->only(['id', 'alias', 'normalized_alias']);
    }
}
