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
        'candidate_profile_id', 'target_job_post_id', 'title', 'description', 'overall_progress', 'status',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['overall_progress' => 'decimal:2'];
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

    /** @return HasMany<RoadmapStep, $this> */
    public function steps(): HasMany
    {
        return $this->hasMany(RoadmapStep::class)->orderBy('step_order');
    }
}
