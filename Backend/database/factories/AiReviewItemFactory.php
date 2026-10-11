<?php

namespace Database\Factories;

use App\Enums\AiReviewEntityType;
use App\Enums\AiReviewOperation;
use App\Enums\AiReviewStatus;
use App\Enums\AiReviewTriggerReason;
use App\Models\AiReviewItem;
use App\Models\CvExtraction;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AiReviewItem> */
class AiReviewItemFactory extends Factory
{
    public function definition(): array
    {
        $proposal = [
            'skills' => [['name' => 'Laravel', 'category' => 'Framework', 'confidence_score' => 0.60]],
            'experiences' => [],
        ];

        return [
            'entity_type' => AiReviewEntityType::CV_EXTRACTION,
            'entity_id' => CvExtraction::factory(),
            'operation' => AiReviewOperation::CV_PROFILE_SYNC,
            'proposed_value' => $proposal,
            'canonical_value' => ['skills' => [], 'experiences' => []],
            'trigger_reason' => AiReviewTriggerReason::LOW_CONFIDENCE,
            'confidence' => '0.6000',
            'source' => 'cv:openai:gpt-4o:1.0.0',
            'payload_hash' => hash('sha256', json_encode($proposal, JSON_THROW_ON_ERROR)),
            'status' => AiReviewStatus::PENDING,
        ];
    }
}
