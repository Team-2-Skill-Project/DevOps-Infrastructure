<?php

namespace App\Http\Resources;

use App\Models\JobPost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin JobPost */
class JobFeedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...$this->only([
                'id', 'title', 'job_type', 'work_mode', 'employment_type', 'experience_level',
                'country', 'state', 'city', 'min_years_experience', 'max_years_experience',
                'source', 'application_method', 'published_at', 'expires_at', 'is_active',
            ]),
            'company' => $this->whenLoaded('company', fn () => $this->company->only(['id', 'name', 'logo_url', 'is_verified', 'is_active'])),
            'required_skills' => JobSkillResource::collection($this->whenLoaded('requiredSkills')),
            'preferred_skills' => JobSkillResource::collection($this->whenLoaded('preferredSkills')),
            'is_expired' => $this->isExpired(),
            'is_saved' => (bool) ($this->resource->getAttribute('is_saved') ?? false),
        ];
    }
}
