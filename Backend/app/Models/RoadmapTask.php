<?php

namespace App\Models;

use App\Enums\RoadmapTaskStatus;
use Database\Factories\RoadmapTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoadmapTask extends Model
{
    /** @use HasFactory<RoadmapTaskFactory> */
    use HasFactory;

    protected $table = 'roadmap_steps';

    protected $fillable = [
        'roadmap_id',
        'roadmap_milestone_id',
        'step_order',
        'task_order',
        'title',
        'description',
        'target_skill_id',
        'status',
        'completed_at',
        'evidence',
        'notes',
        'resources',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'step_order' => 'integer',
            'task_order' => 'integer',
            'status' => RoadmapTaskStatus::class,
            'completed_at' => 'datetime',
            'resources' => 'array',
            'metadata' => 'array',
        ];
    }

    public function isCompleted(): bool
    {
        return $this->getAttribute('status') === RoadmapTaskStatus::COMPLETED;
    }

    public function statusValue(): string
    {
        $status = $this->getAttribute('status');

        return $status instanceof RoadmapTaskStatus
            ? $status->value
            : (string) $status;
    }

    /** @return BelongsTo<Roadmap, $this> */
    public function roadmap(): BelongsTo
    {
        return $this->belongsTo(Roadmap::class);
    }

    /** @return BelongsTo<RoadmapMilestone, $this> */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(RoadmapMilestone::class, 'roadmap_milestone_id');
    }

    /** @return BelongsTo<Skill, $this> */
    public function targetSkill(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'target_skill_id');
    }
}
