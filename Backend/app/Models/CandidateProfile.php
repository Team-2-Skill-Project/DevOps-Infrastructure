<?php

namespace App\Models;

use Database\Factories\CandidateProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CandidateProfile extends Model
{
    /** @use HasFactory<CandidateProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date_of_birth',
        'gender',
        'job_title',
        'country',
        'state',
        'city',
        'github_url',
        'linkedin_url',
        'military_status',
        'professional_summary',
        'profile_completed_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'profile_completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Education, $this> */
    public function educations(): HasMany
    {
        return $this->hasMany(Education::class);
    }

    /** @return HasMany<Experience, $this> */
    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    /** @return HasMany<Project, $this> */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /** @return HasMany<Certificate, $this> */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /** @return HasMany<Language, $this> */
    public function languages(): HasMany
    {
        return $this->hasMany(Language::class);
    }

    /** @return HasOne<CareerPreference, $this> */
    public function careerPreference(): HasOne
    {
        return $this->hasOne(CareerPreference::class);
    }

    /** @return HasMany<CandidateSkill, $this> */
    public function candidateSkills(): HasMany
    {
        return $this->hasMany(CandidateSkill::class);
    }

    /** @return HasMany<CvDocument, $this> */
    public function cvDocuments(): HasMany
    {
        return $this->hasMany(CvDocument::class);
    }

    /** @return BelongsToMany<Skill, $this> */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'candidate_skills')
            ->withPivot(['id', 'source', 'proficiency_level'])
            ->withTimestamps();
    }

    /** @return HasMany<JobMatch, $this> */
    public function jobMatches(): HasMany
    {
        return $this->hasMany(JobMatch::class);
    }

    /** @return HasMany<Roadmap, $this> */
    public function roadmaps(): HasMany
    {
        return $this->hasMany(Roadmap::class);
    }
}
