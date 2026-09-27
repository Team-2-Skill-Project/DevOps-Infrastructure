<?php

namespace App\Models;

use Database\Factories\JobMatchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobMatch extends Model
{
    /** @use HasFactory<JobMatchFactory> */
    use HasFactory;

    protected $fillable = [
        'candidate_profile_id',
        'job_post_id',
        'score',
        'matched_skills',
        'missing_skills',
        'weak_skills',
        'reasons',
        'confidence',
        'calculated_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'confidence' => 'decimal:2',
            'matched_skills' => 'array',
            'missing_skills' => 'array',
            'weak_skills' => 'array',
            'reasons' => 'array',
            'calculated_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<CandidateProfile, $this> */
    public function candidateProfile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class);
    }

    /** @return BelongsTo<JobPost, $this> */
    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }
}
