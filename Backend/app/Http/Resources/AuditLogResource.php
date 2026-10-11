<?php

namespace App\Http\Resources;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AuditLog */
class AuditLogResource extends JsonResource
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
                'id', 'actor_id', 'action', 'entity_type', 'entity_id', 'source',
                'before', 'after', 'metadata', 'created_at',
            ]),
            'actor' => $this->whenLoaded('actor', fn () => $this->actor?->only(['id', 'name'])),
        ];
    }
}
