<?php

namespace App\Http\Resources;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Company */
class CompanyResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            ...$this->only([
                'id', 'name', 'website_url', 'linkedin_url', 'logo_url', 'industry',
                'country', 'state', 'city', 'description', 'is_verified', 'is_active',
                'created_at', 'updated_at',
            ]),
            'aliases' => $this->whenLoaded('aliases', fn () => CompanyAliasResource::collection($this->aliases)),
            'job_posts_count' => $this->when(isset($this->job_posts_count), $this->job_posts_count),
        ];
    }
}
