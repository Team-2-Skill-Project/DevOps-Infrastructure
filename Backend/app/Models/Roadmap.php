<?php

namespace App\Models;

use Database\Factories\RoadmapFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Roadmap extends Model
{
    /** @use HasFactory<RoadmapFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'previous_roadmap_id',
        'candidate_profile_id',
        'target_job_post_id',
        'target_role',
        'title',
        'description',
        'rationale',
        'overall_progress',
        'status',
        'generation_version',
        'generated_at',
        'refreshed_at',
        'progress_at_generation',
        'generation_input_snapshot',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'overall_progress' => 'decimal:2',
            'progress_at_generation' => 'decimal:2',
            'generation_input_snapshot' => 'array',
            'generated_at' => 'datetime',
            'refreshed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Roadmap, $this> */
    public function previousRoadmap(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_roadmap_id');
    }

    /** @return HasMany<Roadmap, $this> */
    public function revisions(): HasMany
    {
        return $this->hasMany(self::class, 'previous_roadmap_id');
    }

    /** @return BelongsTo<CandidateProfile, $this> */
    public function candidateProfile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class);
    }

    /** @return BelongsTo<JobPost, $this> */
    public function targetJobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'target_job_post_id');
    }

    /** @return HasMany<RoadmapPhase, $this> */
    public function phases(): HasMany
    {
        return $this->hasMany(RoadmapPhase::class)->orderBy('phase_order');
    }

    /** @return HasMany<RoadmapTask, $this> */
    public function tasks(): HasMany
    {
        return $this->hasMany(RoadmapTask::class)->orderBy('step_order');
    }

    /** @return HasMany<RoadmapStep, $this> */
    public function steps(): HasMany
    {
        return $this->hasMany(RoadmapStep::class)->orderBy('step_order');
    }
}
