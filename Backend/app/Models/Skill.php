<?php

namespace App\Models;

use Database\Factories\SkillFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skill extends Model
{
    /** @use HasFactory<SkillFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'normalized_name',
        'category',
        'skill_category_id',
    ];

    /** @return HasMany<CandidateSkill, $this> */
    public function candidateSkills(): HasMany
    {
        return $this->hasMany(CandidateSkill::class);
    }

    /** @return HasMany<JobSkill, $this> */
    public function jobSkills(): HasMany
    {
        return $this->hasMany(JobSkill::class);
    }

    /** @return BelongsToMany<CandidateProfile, $this> */
    public function candidateProfiles(): BelongsToMany
    {
        return $this->belongsToMany(CandidateProfile::class, 'candidate_skills')
            ->withPivot(['id', 'source', 'proficiency_level'])
            ->withTimestamps();
    }

    /** @return HasMany<RoadmapStep, $this> */
    public function roadmapSteps(): HasMany
    {
        return $this->hasMany(RoadmapStep::class, 'target_skill_id');
    }

    /** @return BelongsTo<SkillCategory, $this> */
    public function skillCategory(): BelongsTo
    {
        return $this->belongsTo(SkillCategory::class);
    }

    /** @return HasMany<SkillAlias, $this> */
    public function aliases(): HasMany
    {
        return $this->hasMany(SkillAlias::class);
    }
}
