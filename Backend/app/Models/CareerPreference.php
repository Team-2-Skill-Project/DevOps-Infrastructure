<?php

namespace App\Models;

use Database\Factories\CareerPreferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerPreference extends Model
{
    /** @use HasFactory<CareerPreferenceFactory> */
    use HasFactory;

    protected $fillable = [
        'candidate_profile_id',
        'target_role',
        'job_type',
        'work_mode',
        'preferred_country',
        'preferred_city',
        'experience_level',
        'career_goal',
        'open_to_relocation',
        'target_roles',
        'preferred_industries',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'target_roles' => 'array',
            'preferred_industries' => 'array',
            'open_to_relocation' => 'boolean',
        ];
    }

    /** @return BelongsTo<CandidateProfile, $this> */
    public function candidateProfile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class);
    }
}
