<?php

namespace App\Models;

use App\Enums\AiReviewAction;
use Database\Factories\AiReviewAuditFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $ai_review_item_id
 * @property AiReviewAction $action
 * @property int|null $reviewer_id
 * @property array<string, mixed> $original_proposal
 * @property array<string, mixed>|null $canonical_before
 * @property array<string, mixed>|null $applied_value
 * @property string|null $decision_reason
 * @property string|null $reviewer_notes
 * @property Carbon $created_at
 */
class AiReviewAudit extends Model
{
    /** @use HasFactory<AiReviewAuditFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'ai_review_item_id',
        'action',
        'reviewer_id',
        'original_proposal',
        'canonical_before',
        'applied_value',
        'decision_reason',
        'reviewer_notes',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'action' => AiReviewAction::class,
            'original_proposal' => 'array',
            'canonical_before' => 'array',
            'applied_value' => 'array',
        ];
    }

    /** @return BelongsTo<AiReviewItem, $this> */
    public function reviewItem(): BelongsTo
    {
        return $this->belongsTo(AiReviewItem::class, 'ai_review_item_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
