<?php

namespace App\Models;

use Database\Factories\SkillCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SkillCategory extends Model
{
    /** @use HasFactory<SkillCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name', 'normalized_name', 'description', 'is_active',
    ];

    protected static function booted(): void
    {
        static::saving(function (SkillCategory $category): void {
            $category->name = Str::trim($category->name);
            $category->normalized_name = Str::lower($category->name);
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return HasMany<Skill, $this> */
    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }
}
