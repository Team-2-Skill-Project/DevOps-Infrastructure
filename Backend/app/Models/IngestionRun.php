<?php

namespace App\Models;

use Database\Factories\IngestionRunFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IngestionRun extends Model
{
    /** @use HasFactory<IngestionRunFactory> */
    use HasFactory;

    public const STATUS_RUNNING = 'running';

    public const STATUS_SUCCEEDED = 'succeeded';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_FAILED = 'failed';

    public const STATUSES = ['running', 'succeeded', 'partial', 'failed'];

    public const TRIGGER_TYPES = ['scheduled', 'manual', 'retry'];

    public const ERROR_CODES = ['collection_failed', 'parser_error', 'source_unavailable'];

    public const COUNTERS = ['discovered_count', 'fetched_count', 'created_count', 'updated_count', 'skipped_count', 'failed_count'];

    protected $fillable = [
        'job_source_id', 'trigger_type', 'status', 'started_at', 'finished_at',
        'discovered_count', 'fetched_count', 'created_count', 'updated_count', 'skipped_count', 'failed_count',
        'error_code', 'error_message', 'error_context',
    ];

    /**
     * @return array{
     *     started_at: 'datetime', finished_at: 'datetime', error_context: 'array',
     *     discovered_count: 'integer', fetched_count: 'integer', created_count: 'integer',
     *     updated_count: 'integer', skipped_count: 'integer', failed_count: 'integer'
     * }
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime', 'finished_at' => 'datetime', 'error_context' => 'array',
            'discovered_count' => 'integer', 'fetched_count' => 'integer', 'created_count' => 'integer',
            'updated_count' => 'integer', 'skipped_count' => 'integer', 'failed_count' => 'integer',
        ];
    }

    /** @return HasMany<RawJob, $this> */
    public function rawJobs(): HasMany
    {
        return $this->hasMany(RawJob::class);
    }

    /** @return BelongsTo<JobSource, $this> */
    public function jobSource(): BelongsTo
    {
        return $this->belongsTo(JobSource::class);
    }
}
