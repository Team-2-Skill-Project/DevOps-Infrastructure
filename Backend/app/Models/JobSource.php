<?php

namespace App\Models;

use Database\Factories\JobSourceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobSource extends Model
{
    /** @use HasFactory<JobSourceFactory> */
    use HasFactory;

    public const SOURCE_TYPES = ['public', 'authorized', 'manual'];

    public const COLLECTION_METHODS = ['api', 'scraper', 'manual', 'file'];

    protected $fillable = [
        'name', 'slug', 'base_url', 'source_type', 'collection_method',
        'is_active', 'schedule_enabled', 'schedule_expression', 'notes',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'schedule_enabled' => 'boolean'];
    }

    /** @return HasMany<IngestionRun, $this> */
    public function runs(): HasMany
    {
        return $this->hasMany(IngestionRun::class);
    }

    /** @return HasMany<RawJob, $this> */
    public function rawJobs(): HasMany
    {
        return $this->hasMany(RawJob::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
