<?php

namespace App\Http\Resources;

use App\Models\RoadmapMilestone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RoadmapMilestone */
class RoadmapMilestoneResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order' => $this->milestone_order,
            'title' => $this->title,
            'description' => $this->description,
            'progress' => (float) $this->progress,
            'completed_at' => $this->completed_at,
            'tasks' => RoadmapTaskResource::collection($this->whenLoaded('tasks')),
        ];
    }
}
