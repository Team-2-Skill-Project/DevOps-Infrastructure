<?php

namespace Database\Factories;

use App\Enums\AiReviewAction;
use App\Models\AiReviewAudit;
use App\Models\AiReviewItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AiReviewAudit> */
class AiReviewAuditFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ai_review_item_id' => AiReviewItem::factory(),
            'action' => AiReviewAction::APPROVED,
            'reviewer_id' => User::factory(),
            'original_proposal' => ['skills' => [], 'experiences' => []],
            'canonical_before' => ['skills' => [], 'experiences' => []],
            'applied_value' => ['skills' => [], 'experiences' => []],
            'decision_reason' => 'Confirmed against the CV.',
        ];
    }
}
