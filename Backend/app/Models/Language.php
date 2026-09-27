<?php

namespace App\Models;

use Database\Factories\LanguageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Language extends Model
{
    /** @use HasFactory<LanguageFactory> */
    use HasFactory;

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_CV_EXTRACTED = 'cv_extracted';

    protected $fillable = [
        'candidate_profile_id',
        'language',
        'proficiency_level',
        'source',
    ];

    /** @return BelongsTo<CandidateProfile, $this> */
    public function candidateProfile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class);
    }
}
