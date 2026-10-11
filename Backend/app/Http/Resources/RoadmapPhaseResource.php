<?php

namespace App\Http\Resources;

use App\Models\RoadmapPhase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RoadmapPhase */
class RoadmapPhaseResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order' => $this->phase_order,
            'title' => $this->title,
            'description' => $this->description,
            'rationale' => $this->rationale,
            'progress' => (float) $this->progress,
            'milestones' => RoadmapMilestoneResource::collection($this->whenLoaded('milestones')),
        ];
    }
}
