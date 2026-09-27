<?php

namespace App\Models;

use Database\Factories\RoadmapStepFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoadmapStep extends Model
{
    /** @use HasFactory<RoadmapStepFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'roadmap_id', 'step_order', 'title', 'description', 'target_skill_id', 'status', 'resources',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['resources' => 'array'];
    }

    /** @return BelongsTo<Roadmap, $this> */
    public function roadmap(): BelongsTo
    {
        return $this->belongsTo(Roadmap::class);
    }

    /** @return BelongsTo<Skill, $this> */
    public function targetSkill(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'target_skill_id');
    }
}
