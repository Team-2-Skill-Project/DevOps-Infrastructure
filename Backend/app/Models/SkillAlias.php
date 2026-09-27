<?php

namespace App\Models;

use Database\Factories\SkillAliasFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SkillAlias extends Model
{
    /** @use HasFactory<SkillAliasFactory> */
    use HasFactory;

    protected $fillable = [
        'skill_id', 'alias', 'normalized_alias',
    ];

    protected static function booted(): void
    {
        static::saving(function (SkillAlias $alias): void {
            $alias->alias = Str::trim($alias->alias);
            $alias->normalized_alias = Str::lower($alias->alias);
        });
    }

    /** @return BelongsTo<Skill, $this> */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
