<?php

namespace App\Models;

use Database\Factories\JobSkillFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSkill extends Model
{
    /** @use HasFactory<JobSkillFactory> */
    use HasFactory;

    protected $fillable = [
        'job_post_id',
        'skill_id',
        'is_required',
        'importance',
        'required_level',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'importance' => 'integer',
        ];
    }

    /** @return BelongsTo<JobPost, $this> */
    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    /** @return BelongsTo<Skill, $this> */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
