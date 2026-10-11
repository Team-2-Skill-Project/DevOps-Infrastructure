<?php

namespace App\Http\Resources;

use App\Models\RoadmapTask;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RoadmapTask */
class RoadmapTaskResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order' => $this->task_order,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->statusValue(),
            'completed_at' => $this->completed_at,
            'evidence' => $this->evidence,
            'notes' => $this->notes,
            'metadata' => $this->metadata,
        ];
    }
}
