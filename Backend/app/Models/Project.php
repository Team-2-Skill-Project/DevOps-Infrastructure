<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_CV_EXTRACTED = 'cv_extracted';

    protected $fillable = [
        'candidate_profile_id',
        'name',
        'description',
        'technologies',
        'project_url',
        'github_url',
        'start_date',
        'end_date',
        'source',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'technologies' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /** @return BelongsTo<CandidateProfile, $this> */
    public function candidateProfile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class);
    }
}
