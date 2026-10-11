<?php

namespace App\Models;

use Database\Factories\RoadmapPhaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoadmapPhase extends Model
{
    /** @use HasFactory<RoadmapPhaseFactory> */
    use HasFactory;

    protected $fillable = [
        'roadmap_id',
        'phase_order',
        'title',
        'description',
        'rationale',
        'progress',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'phase_order' => 'integer',
            'progress' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Roadmap, $this> */
    public function roadmap(): BelongsTo
    {
        return $this->belongsTo(Roadmap::class);
    }

    /** @return HasMany<RoadmapMilestone, $this> */
    public function milestones(): HasMany
    {
        return $this->hasMany(RoadmapMilestone::class)->orderBy('milestone_order');
    }
}
