<?php

namespace App\Http\Resources;

use App\Models\IngestionRun;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin IngestionRun */
class IngestionRunResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return $this->only([
            'id', 'job_source_id', 'trigger_type', 'status', 'started_at', 'finished_at',
            'discovered_count', 'fetched_count', 'created_count', 'updated_count', 'skipped_count', 'failed_count',
            'error_code', 'error_message', 'error_context', 'created_at', 'updated_at',
        ]);
    }
}
