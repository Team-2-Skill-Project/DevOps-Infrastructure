<?php

namespace App\Models;

use App\Enums\AiReviewEntityType;
use App\Enums\AiReviewOperation;
use App\Enums\AiReviewStatus;
use App\Enums\AiReviewTriggerReason;
use Database\Factories\AiReviewItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property AiReviewEntityType $entity_type
 * @property int $entity_id
 * @property AiReviewOperation $operation
 * @property array<string, mixed> $proposed_value
 * @property array<string, mixed>|null $canonical_value
 * @property AiReviewTriggerReason $trigger_reason
 * @property string|null $confidence
 * @property string|null $source
 * @property string $payload_hash
 * @property AiReviewStatus $status
 * @property int|null $reviewer_id
 * @property Carbon|null $reviewed_at
 * @property string|null $decision_reason
 * @property string|null $reviewer_notes
 * @property array<string, mixed>|null $corrected_value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property User|null $reviewer
 * @property Collection<int, AiReviewAudit> $audits
 */
class AiReviewItem extends Model
{
    /** @use HasFactory<AiReviewItemFactory> */
    use HasFactory;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'operation',
        'proposed_value',
        'canonical_value',
        'trigger_reason',
        'confidence',
        'source',
        'payload_hash',
        'status',
        'reviewer_id',
        'reviewed_at',
        'decision_reason',
        'reviewer_notes',
        'corrected_value',
    ];

    protected $attributes = [
        'status' => AiReviewStatus::PENDING->value,
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'entity_type' => AiReviewEntityType::class,
            'operation' => AiReviewOperation::class,
            'proposed_value' => 'array',
            'canonical_value' => 'array',
            'trigger_reason' => AiReviewTriggerReason::class,
            'confidence' => 'decimal:4',
            'status' => AiReviewStatus::class,
            'reviewed_at' => 'datetime',
            'corrected_value' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /** @return BelongsTo<CvExtraction, $this> */
    public function cvExtraction(): BelongsTo
    {
        return $this->belongsTo(CvExtraction::class, 'entity_id');
    }

    /** @return HasMany<AiReviewAudit, $this> */
    public function audits(): HasMany
    {
        return $this->hasMany(AiReviewAudit::class);
    }

    public function isPending(): bool
    {
        return $this->status === AiReviewStatus::PENDING;
    }
}
