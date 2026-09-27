<?php

namespace App\Models;

use Database\Factories\CandidateSkillFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $confidence
 * @property array<array-key, mixed>|null $evidence
 */
class CandidateSkill extends Model
{
    /** @use HasFactory<CandidateSkillFactory> */
    use HasFactory;

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_CV_EXTRACTED = 'cv_extracted';

    public const SOURCE_NORMALIZED = 'normalized';

    public const SOURCE_AI_SUGGESTED = 'ai_suggested';

    protected $fillable = [
        'candidate_profile_id',
        'skill_id',
        'source',
        'proficiency_level',
        'confidence',
        'evidence',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'confidence' => 'decimal:2',
            'evidence' => 'array',
        ];
    }

    /** @return BelongsTo<CandidateProfile, $this> */
    public function candidateProfile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class);
    }

    /** @return BelongsTo<Skill, $this> */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
