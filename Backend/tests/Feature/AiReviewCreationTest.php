<?php

use App\Enums\AiReviewEntityType;
use App\Enums\AiReviewOperation;
use App\Enums\AiReviewStatus;
use App\Enums\AiReviewTriggerReason;
use App\Models\AiReviewItem;
use App\Models\CandidateSkill;
use App\Models\CvDocument;
use App\Models\CvExtraction;
use App\Models\Experience;
use App\Services\AiReviewService;
use Illuminate\Support\Facades\DB;

function lowConfidenceCvPayload(): array
{
    return [
        'skills' => [[
            'name' => 'Laravel',
            'category' => 'Framework',
            'proficiency_level' => 'advanced',
            'confidence_score' => 0.61,
            'evidence' => ['Built APIs'],
        ]],
        'experiences' => [[
            'company_name' => 'Acme',
            'title' => 'Backend Engineer',
            'start_date' => '2024-01-01',
        ]],
    ];
}

it('creates a pending review for a low-confidence completed CV extraction without changing canonical data', function () {
    $extraction = CvExtraction::factory()->create([
        'confidence_score' => '0.6100',
        'extracted_data' => lowConfidenceCvPayload(),
    ]);

    $item = AiReviewItem::query()->sole();

    expect($item->entity_type)->toBe(AiReviewEntityType::CV_EXTRACTION)
        ->and($item->entity_id)->toBe($extraction->id)
        ->and($item->operation)->toBe(AiReviewOperation::CV_PROFILE_SYNC)
        ->and($item->trigger_reason)->toBe(AiReviewTriggerReason::LOW_CONFIDENCE)
        ->and($item->status)->toBe(AiReviewStatus::PENDING)
        ->and($item->confidence)->toBe('0.6100')
        ->and($item->proposed_value)->toBe(lowConfidenceCvPayload())
        ->and($item->cvExtraction->is($extraction))->toBeTrue();

    expect(CandidateSkill::query()->count())->toBe(0)
        ->and(Experience::query()->count())->toBe(0);
});

it('does not create reviews at or above the configured confidence threshold', function (string $confidence) {
    config()->set('ai_review.cv_extraction_confidence_threshold', 0.75);

    CvExtraction::factory()->create([
        'confidence_score' => $confidence,
        'extracted_data' => lowConfidenceCvPayload(),
    ]);

    expect(AiReviewItem::query()->count())->toBe(0);
})->with(['0.7500', '0.9500']);

it('does not create reviews for unfinished or already verified extractions', function (array $attributes) {
    CvExtraction::factory()->create($attributes);

    expect(AiReviewItem::query()->count())->toBe(0);
})->with([
    'pending' => [['status' => 'pending', 'confidence_score' => '0.1000']],
    'verified' => [['confidence_score' => '0.1000', 'extracted_data' => ['verified_payload' => lowConfidenceCvPayload()]]],
]);

it('reuses the same review item when extraction processing is retried with the same result', function () {
    $extraction = CvExtraction::factory()->create([
        'confidence_score' => '0.6100',
        'extracted_data' => lowConfidenceCvPayload(),
    ]);
    $first = AiReviewItem::query()->sole();

    app(AiReviewService::class)->createForCvExtraction($extraction);
    $extraction->touch();

    expect(AiReviewItem::query()->count())->toBe(1)
        ->and(AiReviewItem::query()->sole()->is($first))->toBeTrue();
});

it('rolls review creation back with the extraction transaction', function () {
    $document = CvDocument::factory()->create();

    expect(fn () => DB::transaction(function () use ($document): void {
        CvExtraction::factory()->for($document)->create([
            'confidence_score' => '0.6100',
            'extracted_data' => lowConfidenceCvPayload(),
        ]);

        throw new RuntimeException('rollback');
    }))->toThrow(RuntimeException::class);

    expect(CvExtraction::query()->count())->toBe(0)
        ->and(AiReviewItem::query()->count())->toBe(0);
});
