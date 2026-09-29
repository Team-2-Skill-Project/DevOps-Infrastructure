<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static \Database\Factories\ApplicationFactory factory($count = null, $state = [])
 */
class Application extends Model
{
    /** @use HasFactory<ApplicationFactory> */
    use HasFactory;

    protected $fillable = [
        'candidate_profile_id',
        'job_id',
        'status',
        'cover_letter',
    ];

    protected $casts = [
        'status' => ApplicationStatus::class,
    ];

    /**
     * @return BelongsTo<CandidateProfile, $this>
     */
    public function candidateProfile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class);
    }

    /**
     * @return BelongsTo<JobPost, $this>
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    /**
     * @return HasMany<ApplicationStatusHistory, $this>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class);
    }
}
