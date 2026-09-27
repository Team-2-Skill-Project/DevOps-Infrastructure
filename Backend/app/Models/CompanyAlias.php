<?php

namespace App\Models;

use Database\Factories\CompanyAliasFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CompanyAlias extends Model
{
    /** @use HasFactory<CompanyAliasFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'alias',
        'normalized_alias',
    ];

    protected static function booted(): void
    {
        static::creating(function (CompanyAlias $alias): void {
            if ($alias->getAttribute('normalized_alias') === null) {
                $alias->normalized_alias = Str::lower(Str::trim($alias->alias));
            }
        });
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
