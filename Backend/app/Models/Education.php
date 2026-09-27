<?php

namespace App\Models;

use Database\Factories\EducationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Education extends Model
{
    /** @use HasFactory<EducationFactory> */
    use HasFactory;

    protected $table = 'educations';

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_CV_EXTRACTED = 'cv_extracted';

    protected $fillable = [
        'candidate_profile_id',
        'education_level',
        'institution',
        'field_of_study',
        'degree',
        'start_date',
        'end_date',
        'is_current',
        'grade',
        'description',
        'source',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
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
