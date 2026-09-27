<?php

namespace App\Models;

use Database\Factories\ExperienceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Experience extends Model
{
    /** @use HasFactory<ExperienceFactory> */
    use HasFactory;

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_CV_EXTRACTED = 'cv_extracted';

    protected $fillable = [
        'candidate_profile_id',
        'job_title',
        'company_name',
        'employment_type',
        'country',
        'city',
        'start_date',
        'end_date',
        'is_current',
        'description',
        'source',
        'technologies',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'technologies' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    /** @return BelongsTo<CandidateProfile, $this> */
    public function candidateProfile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class);
    }
}
