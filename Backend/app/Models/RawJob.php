<?php

namespace App\Models;

use Database\Factories\RawJobFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RawJob extends Model
{
    /** @use HasFactory<RawJobFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_EXTRACTED = 'extracted';

    public const STATUS_FAILED = 'failed';

    public const STATUSES = ['pending', 'extracted', 'failed'];

    public const NORMALIZATION_PENDING = 'pending';

    public const NORMALIZATION_NORMALIZED = 'normalized';

    public const NORMALIZATION_FAILED = 'failed';

    public const NORMALIZATION_STATUSES = ['pending', 'normalized', 'failed'];

    public const NORMALIZATION_ERROR_CODES = ['invalid_data', 'unsupported_value'];

    public const ERROR_CODES = ['extraction_failed', 'invalid_payload', 'unsupported_format'];

    public const DEDUPLICATION_PENDING = 'pending';

    public const DEDUPLICATION_MATCHED = 'matched';

    public const DEDUPLICATION_POSSIBLE = 'possible';

    public const DEDUPLICATION_DISTINCT = 'distinct';

    public const DEDUPLICATION_FAILED = 'failed';

    public const DEDUPLICATION_STATUSES = ['pending', 'matched', 'possible', 'distinct', 'failed'];

    public const DEDUPLICATION_METHODS = ['existing_reference', 'same_source_external_id', 'exact_normalized_url', 'fingerprint_candidate', 'title_company_similarity', 'no_match'];

    protected $fillable = [
        'job_source_id', 'ingestion_run_id', 'identity_key', 'external_id', 'source_url', 'detail_url',
        'discovered_title', 'discovered_company_name', 'discovered_at', 'discovery_metadata',
        'raw_payload', 'raw_text', 'extraction_status', 'extraction_error_code', 'extraction_error_message', 'extracted_data', 'extracted_at',
        'normalization_status', 'normalization_error_code', 'normalization_error_message', 'normalized_data', 'normalized_at',
        'deduplication_status', 'fingerprint', 'fingerprint_version', 'canonical_job_post_id', 'deduplication_method', 'deduplication_evidence', 'deduplicated_at',
    ];

    /** @return array{discovered_at: 'datetime', extracted_at: 'datetime', normalized_at: 'datetime', deduplicated_at: 'datetime', discovery_metadata: 'array', raw_payload: 'array', extracted_data: 'array', normalized_data: 'array', deduplication_evidence: 'array'} */
    protected function casts(): array
    {
        return [
            'discovered_at' => 'datetime', 'extracted_at' => 'datetime', 'normalized_at' => 'datetime', 'deduplicated_at' => 'datetime',
            'discovery_metadata' => 'array', 'raw_payload' => 'array', 'extracted_data' => 'array', 'normalized_data' => 'array',
            'deduplication_evidence' => 'array',
        ];
    }

    /** @return BelongsTo<JobSource, $this> */
    public function jobSource(): BelongsTo
    {
        return $this->belongsTo(JobSource::class);
    }

    /** @return BelongsTo<IngestionRun, $this> */
    public function ingestionRun(): BelongsTo
    {
        return $this->belongsTo(IngestionRun::class);
    }

    /** @return BelongsTo<JobPost, $this> */
    public function canonicalJobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'canonical_job_post_id');
    }

    /** @return HasOne<JobSourceReference, $this> */
    public function sourceReference(): HasOne
    {
        return $this->hasOne(JobSourceReference::class, 'raw_job_id');
    }
}
