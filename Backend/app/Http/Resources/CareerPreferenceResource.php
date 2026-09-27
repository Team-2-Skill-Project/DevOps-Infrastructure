<?php

namespace App\Http\Resources;

use App\Models\CareerPreference;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CareerPreference */
class CareerPreferenceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return $this->only([
            'id', 'target_role', 'job_type', 'work_mode', 'preferred_country',
            'preferred_city', 'experience_level', 'career_goal', 'open_to_relocation', 'target_roles', 'preferred_industries',
        ]);
    }
}
