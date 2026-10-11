<?php

namespace App\Http\Resources;

use App\Models\Roadmap;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Roadmap */
class RoadmapResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'previous_roadmap_id' => $this->previous_roadmap_id,
            'title' => $this->title,
            'description' => $this->description,
            'rationale' => $this->rationale,
            'status' => $this->status,
            'progress' => (float) $this->overall_progress,
            'target' => $this->target(),
            'generation_version' => $this->generation_version,
            'generated_at' => $this->generated_at,
            'refreshed_at' => $this->refreshed_at,
            'phases' => RoadmapPhaseResource::collection($this->whenLoaded('phases')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /** @return array<string, mixed> */
    private function target(): array
    {
        if ($this->target_job_post_id !== null) {
            return [
                'type' => 'job',
                'id' => $this->target_job_post_id,
                'title' => $this->targetJobPost?->title,
                'canonical_role' => $this->targetJobPost?->canonical_role,
                'company' => $this->targetJobPost?->company?->only(['id', 'name']),
            ];
        }

        return ['type' => 'role', 'role' => $this->target_role];
    }
}
