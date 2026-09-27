<?php

namespace App\Models;

use Database\Factories\JobPostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class JobPost extends Model
{
    public const TYPE_JOB = 'job';

    public const TYPE_INTERNSHIP = 'internship';

    public const SOURCE_DIRECT = 'direct';

    public const SOURCE_EXTERNAL_API = 'external_api';

    public const APPLICATION_INTERNAL = 'internal';

    public const APPLICATION_EXTERNAL = 'external';

    /** @use HasFactory<JobPostFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'created_by',
        'title',
        'job_type',
        'description',
        'employment_type',
        'work_mode',
        'experience_level',
        'country',
        'state',
        'city',
        'salary_min',
        'salary_max',
        'salary_currency',
        'application_url',
        'application_method',
        'source',
        'external_id',
        'external_url',
        'status',
        'is_active',
        'published_at',
        'expires_at',
        'min_years_experience',
        'max_years_experience',
        'responsibilities',
        'canonical_role',
    ];

    protected function casts(): array
    {
        return [
            'min_years_experience' => 'integer',
            'max_years_experience' => 'integer',
            'responsibilities' => 'array',
            'salary_min' => 'decimal:2',
            'salary_max' => 'decimal:2',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function isExpired(): bool
    {
        $expiresAt = $this->getRawOriginal('expires_at');

        return $expiresAt !== null && Carbon::parse((string) $expiresAt)->isPast();
    }

    public function isExternallyApplied(): bool
    {
        return $this->application_method === self::APPLICATION_EXTERNAL;
    }

    /** @param Builder<JobPost> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** @param Builder<JobPost> $query */
    public function scopeNotExpired(Builder $query): void
    {
        $query->where(function (Builder $query): void {
            $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<JobSkill, $this> */
    public function jobSkills(): HasMany
    {
        return $this->hasMany(JobSkill::class);
    }

    /** @return BelongsToMany<Skill, $this> */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'job_skills')
            ->withPivot(['id', 'is_required', 'importance', 'required_level'])
            ->withTimestamps();
    }

    /** @return BelongsToMany<Skill, $this> */
    public function requiredSkills(): BelongsToMany
    {
        return $this->skills()->wherePivot('is_required', true);
    }

    /** @return BelongsToMany<Skill, $this> */
    public function preferredSkills(): BelongsToMany
    {
        return $this->skills()->wherePivot('is_required', false);
    }

    /** @return HasMany<JobMatch, $this> */
    public function jobMatches(): HasMany
    {
        return $this->hasMany(JobMatch::class);
    }

    /** @return HasMany<Roadmap, $this> */
    public function roadmaps(): HasMany
    {
        return $this->hasMany(Roadmap::class, 'target_job_post_id');
    }

    /** @return HasMany<SavedJob, $this> */
    public function savedJobs(): HasMany
    {
        return $this->hasMany(SavedJob::class);
    }

    /** @return HasMany<JobSourceReference, $this> */
    public function sourceReferences(): HasMany
    {
        return $this->hasMany(JobSourceReference::class);
    }
}
