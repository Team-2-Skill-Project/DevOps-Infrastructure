<?php

namespace App\Models;

use Database\Factories\JobSourceReferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSourceReference extends Model
{
    /** @use HasFactory<JobSourceReferenceFactory> */
    use HasFactory;

    public const METHOD_EXISTING_REFERENCE = 'existing_reference';

    public const METHOD_SAME_SOURCE_EXTERNAL_ID = 'same_source_external_id';

    public const METHOD_EXACT_NORMALIZED_URL = 'exact_normalized_url';

    public const METHODS = ['existing_reference', 'same_source_external_id', 'exact_normalized_url'];

    protected $fillable = [
        'job_post_id', 'raw_job_id', 'job_source_id', 'ingestion_run_id',
        'external_id', 'source_url', 'detail_url', 'match_method', 'match_evidence',
    ];

    /** @return array{match_evidence: 'array'} */
    protected function casts(): array
    {
        return ['match_evidence' => 'array'];
    }

    /** @return BelongsTo<JobPost, $this> */
    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    /** @return BelongsTo<RawJob, $this> */
    public function rawJob(): BelongsTo
    {
        return $this->belongsTo(RawJob::class);
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
}
