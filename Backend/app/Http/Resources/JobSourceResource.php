<?php

namespace App\Http\Resources;

use App\Models\JobSource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin JobSource */
class JobSourceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return $this->only([
            'id', 'name', 'slug', 'base_url', 'source_type', 'collection_method',
            'is_active', 'schedule_enabled', 'schedule_expression', 'notes',
            'created_by', 'created_at', 'updated_at',
        ]);
    }
}
