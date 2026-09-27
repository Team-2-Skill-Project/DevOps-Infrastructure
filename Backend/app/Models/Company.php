<?php

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'normalized_name',
        'website_url',
        'linkedin_url',
        'logo_url',
        'industry',
        'country',
        'state',
        'city',
        'description',
        'is_verified',
        'is_active',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (Company $company): void {
            if ($company->getAttribute('normalized_name') === null) {
                $company->normalized_name = Str::lower(Str::trim($company->name));
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<CompanyAlias, $this> */
    public function aliases(): HasMany
    {
        return $this->hasMany(CompanyAlias::class);
    }

    /** @return HasMany<JobPost, $this> */
    public function jobPosts(): HasMany
    {
        return $this->hasMany(JobPost::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
