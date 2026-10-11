<?php

namespace App\Services;

use App\Enums\AiReviewEntityType;
use App\Enums\AiReviewOperation;
use App\Enums\AiReviewStatus;
use App\Enums\AiReviewTriggerReason;
use App\Enums\CvExtractionStatus;
use App\Models\AiReviewItem;
use App\Models\CvExtraction;
use Illuminate\Database\UniqueConstraintViolationException;

class AiReviewService
{
    public function __construct(private CvExtractionReviewHandler $cvHandler) {}

    public function createForCvExtraction(CvExtraction $extraction): ?AiReviewItem
    {
        if (! $this->requiresReview($extraction)) {
            return null;
        }

        $proposal = $extraction->extracted_data;
        if (! is_array($proposal)) {
            return null;
        }

        $payloadHash = hash('sha256', json_encode($proposal, JSON_THROW_ON_ERROR));
        $identity = [
            'entity_type' => AiReviewEntityType::CV_EXTRACTION,
            'entity_id' => $extraction->id,
            'operation' => AiReviewOperation::CV_PROFILE_SYNC,
            'payload_hash' => $payloadHash,
        ];

        try {
            return AiReviewItem::query()->firstOrCreate($identity, [
                'proposed_value' => $proposal,
                'canonical_value' => $this->cvHandler->snapshot($extraction),
                'trigger_reason' => AiReviewTriggerReason::LOW_CONFIDENCE,
                'confidence' => $extraction->confidence_score,
                'source' => $this->source($extraction),
                'status' => AiReviewStatus::PENDING,
            ]);
        } catch (UniqueConstraintViolationException) {
            return AiReviewItem::query()->where($identity)->firstOrFail();
        }
    }

    private function requiresReview(CvExtraction $extraction): bool
    {
        if (! in_array($extraction->status, [CvExtractionStatus::SUCCESS, CvExtractionStatus::COMPLETED], true)
            || $extraction->confidence_score === null) {
            return false;
        }

        if (is_array($extraction->extracted_data) && array_key_exists('verified_payload', $extraction->extracted_data)) {
            return false;
        }

        return (float) $extraction->confidence_score < (float) config('ai_review.cv_extraction_confidence_threshold', 0.75);
    }

    private function source(CvExtraction $extraction): string
    {
        return implode(':', array_map(
            static fn (?string $part): string => $part === null || trim($part) === '' ? 'unknown' : trim($part),
            ['cv', $extraction->provider, $extraction->model, $extraction->parser_version],
        ));
    }
}
