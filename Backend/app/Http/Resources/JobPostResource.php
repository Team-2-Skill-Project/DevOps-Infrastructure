<?php

namespace App\Http\Resources;

use App\Models\JobPost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin JobPost */
class JobPostResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            ...$this->only([
                'id', 'title', 'job_type', 'description', 'employment_type', 'work_mode',
                'experience_level', 'country', 'state', 'city', 'salary_min', 'salary_max',
                'salary_currency', 'status', 'is_active', 'application_method',
                'application_url', 'source', 'external_url', 'published_at', 'expires_at',
                'min_years_experience', 'max_years_experience', 'responsibilities',
                'canonical_role', 'created_at', 'updated_at',
            ]),
            'company' => $this->whenLoaded('company', fn () => $this->company->only(['id', 'name', 'logo_url', 'industry', 'country', 'city'])),
            'required_skills' => JobSkillResource::collection($this->whenLoaded('requiredSkills')),
            'preferred_skills' => JobSkillResource::collection($this->whenLoaded('preferredSkills')),
            'is_expired' => $this->isExpired(),
        ];
    }
}
