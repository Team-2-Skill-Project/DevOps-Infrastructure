<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\EmailOtpService;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

#[Fillable(['name', 'email', 'phone', 'password'])]
#[Hidden([
    'password',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'remember_token',
])]
class User extends Authenticatable implements JWTSubject, MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /** @return HasOne<CandidateProfile, $this> */
    public function candidateProfile(): HasOne
    {
        return $this->hasOne(CandidateProfile::class);
    }

    /** @return HasMany<Company, $this> */
    public function companiesCreated(): HasMany
    {
        return $this->hasMany(Company::class, 'created_by');
    }

    /** @return HasMany<JobPost, $this> */
    public function jobPostsCreated(): HasMany
    {
        return $this->hasMany(JobPost::class, 'created_by');
    }

    /** @return HasMany<SavedJob, $this> */
    public function savedJobs(): HasMany
    {
        return $this->hasMany(SavedJob::class);
    }

    /** @return BelongsToMany<JobPost, $this> */
    public function savedJobPosts(): BelongsToMany
    {
        return $this->belongsToMany(JobPost::class, 'saved_jobs')->withTimestamps();
    }

    /** @return HasMany<Notification, $this> */
    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /** @return HasMany<AiReviewItem, $this> */
    public function aiReviewItems(): HasMany
    {
        return $this->hasMany(AiReviewItem::class, 'reviewer_id');
    }

    /** @return HasMany<AiReviewAudit, $this> */
    public function aiReviewAudits(): HasMany
    {
        return $this->hasMany(AiReviewAudit::class, 'reviewer_id');
    }

    public function sendEmailVerificationNotification(): void
    {
        app(EmailOtpService::class)->send($this, EmailOtp::EMAIL_VERIFICATION);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /** @return HasManyThrough<Application, CandidateProfile, $this> */
    public function jobApplications(): HasManyThrough
    {
        return $this->hasManyThrough(
            Application::class,
            CandidateProfile::class,
            'user_id',
            'candidate_profile_id',
            'id',
            'id',
        );
    }

    /** @return HasManyThrough<Roadmap, CandidateProfile, $this> */
    public function roadmaps(): HasManyThrough
    {
        return $this->hasManyThrough(
            Roadmap::class,
            CandidateProfile::class,
            'user_id',
            'candidate_profile_id',
            'id',
            'id',
        );
    }

    /** @return array<string, mixed> */
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
