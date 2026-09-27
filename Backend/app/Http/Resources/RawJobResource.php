<?php

namespace App\Http\Resources;

use App\Models\RawJob;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RawJob */
class RawJobResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $data = $this->only([
            'id', 'job_source_id', 'ingestion_run_id', 'external_id', 'source_url', 'detail_url',
            'discovered_title', 'discovered_company_name', 'discovered_at', 'extraction_status',
            'extraction_error_code', 'extraction_error_message', 'extracted_at', 'normalization_status',
            'normalization_error_code', 'normalization_error_message', 'normalized_at', 'created_at', 'updated_at',
            'deduplication_status', 'fingerprint_version', 'canonical_job_post_id', 'deduplication_method', 'deduplicated_at',
        ]);

        if ($request->route('rawJob') !== null) {
            return [...$data, ...$this->only(['discovery_metadata', 'raw_payload', 'raw_text', 'extracted_data', 'normalized_data', 'fingerprint', 'deduplication_evidence'])];
        }

        return $data;
    }
}
