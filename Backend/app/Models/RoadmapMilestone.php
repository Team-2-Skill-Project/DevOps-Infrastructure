<?php

namespace App\Models;

use Database\Factories\RoadmapMilestoneFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoadmapMilestone extends Model
{
    /** @use HasFactory<RoadmapMilestoneFactory> */
    use HasFactory;

    protected $fillable = [
        'roadmap_phase_id',
        'milestone_order',
        'title',
        'description',
        'progress',
        'completed_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'milestone_order' => 'integer',
            'progress' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<RoadmapPhase, $this> */
    public function phase(): BelongsTo
    {
        return $this->belongsTo(RoadmapPhase::class, 'roadmap_phase_id');
    }

    /** @return HasMany<RoadmapTask, $this> */
    public function tasks(): HasMany
    {
        return $this->hasMany(RoadmapTask::class)->orderBy('task_order');
    }
}
