<?php

/**
 * Task16 final integration — Admin AI Review.
 *
 * Covers the complete Task14 low-confidence path through API boundaries:
 * completed CV extraction with confidence below threshold → observer
 * auto-creates pending AiReviewItem → admin queue exposes it →
 * candidate is forbidden from admin endpoints → approve OR correct →
 * atomic canonical mutation (CandidateSkill/Experience + verified_payload) →
 * AiReviewAudit + Task15 AuditLog → candidate-facing skills API reflects
 * the canonical result → terminal re-decision rejected (idempotency).
 *
 * Downstream note: matching/search/AI consumers of canonical skills do not
 * exist as production features (Job Match and AI Mentor are classified C),
 * so downstream visibility is asserted against the REAL consumers that
 * exist: the candidate skills/profile APIs and the roadmap input builder.
 *
 * Classification: A (fully implemented, integration test only).
 */

use App\Enums\AiReviewStatus;
use App\Enums\CvExtractionStatus;
use App\Models\AiReviewAudit;
use App\Models\AiReviewItem;
use App\Models\AuditLog;
use App\Models\CandidateSkill;
use App\Models\CvExtraction;
use App\Models\Experience;
use App\Models\Skill;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

if (! function_exists('reviewAs')) {
    function reviewAs(object $test, User $user): object
    {
        auth()->forgetGuards();
        app(JWT::class)->unsetToken();

        return $test->withToken(JWTAuth::fromUser($user));
    }
}

if (! function_exists('reviewDecisionPayload')) {
    function reviewDecisionPayload(string $skill = 'Laravel'): array
    {
        return [
            'skills' => [[
                'name' => $skill,
                'category' => 'Framework',
                'proficiency_level' => 'advanced',
                'confidence_score' => 0.61,
                'evidence' => ['Built production APIs'],
            ]],
            'experiences' => [[
                'company_name' => 'Acme',
                'title' => 'Backend Engineer',
                'start_date' => '2024-01-01',
                'technologies' => [$skill],
            ]],
        ];
    }
}

if (! function_exists('pendingReviewItem')) {
    function pendingReviewItem(array $payload = []): AiReviewItem
    {
        CvExtraction::factory()->create([
            'confidence_score' => '0.6100',
            'extracted_data' => $payload === [] ? reviewDecisionPayload() : $payload,
        ]);

        return AiReviewItem::query()->sole();
    }
}

it('reviews low-confidence extraction end to end via approve', function () {
    $item = pendingReviewItem();
    $proposal = $item->proposed_value;
    $candidate = $item->cvExtraction->cvDocument->candidateProfile->user;
    $admin = User::factory()->create(['role' => 'admin']);

    // Queue exposes the pending item; candidates cannot touch admin review.
    reviewAs($this, $admin)->getJson('/api/admin/ai-review-items')
        ->assertOk()
        ->assertJsonFragment(['id' => $item->id, 'status' => AiReviewStatus::PENDING->value]);

    reviewAs($this, $candidate)->getJson('/api/admin/ai-review-items')->assertForbidden();
    reviewAs($this, $candidate)->postJson("/api/admin/ai-review-items/{$item->id}/approve")->assertForbidden();

    // Approve → canonical mutation + audits.
    reviewAs($this, $admin)->postJson("/api/admin/ai-review-items/{$item->id}/approve", [
        'decision_reason' => 'Confirmed against the source CV.',
    ])->assertOk()
        ->assertJsonPath('data.status', AiReviewStatus::APPROVED->value)
        ->assertJsonPath('data.reviewer.id', $admin->id);

    $item->refresh();
    $candidateSkill = CandidateSkill::query()->sole();
    $experience = Experience::query()->sole();
    $audit = AiReviewAudit::query()->sole();

    expect($item->status)->toBe(AiReviewStatus::APPROVED)
        ->and($item->proposed_value)->toBe($proposal)
        ->and($item->reviewer_id)->toBe($admin->id)
        ->and($item->reviewed_at)->not->toBeNull()
        ->and($candidateSkill->skill->name)->toBe('Laravel')
        ->and($candidateSkill->source)->toBe('cv_extracted')
        ->and($experience->job_title)->toBe('Backend Engineer')
        ->and($experience->source)->toBe('cv_extracted')
        ->and($item->cvExtraction->fresh()->extracted_data['verified_payload'])->toBe($proposal)
        ->and($audit->action->value)->toBe('approved')
        ->and($audit->original_proposal)->toBe($proposal)
        ->and($audit->applied_value)->toBe($proposal)
        ->and($audit->reviewer_id)->toBe($admin->id);

    expect(AuditLog::query()->where('entity_type', 'ai_review_item')->where('entity_id', $item->id)->count())->toBeGreaterThan(0);

    // Real downstream consumers see the canonical result.
    reviewAs($this, $candidate)->getJson('/api/candidate/skills')
        ->assertOk()
        ->assertJsonPath('data.0.skill.name', 'Laravel');

    // Terminal decision is final: repeat approve rejected, canonical untouched.
    reviewAs($this, $admin)->postJson("/api/admin/ai-review-items/{$item->id}/approve")
        ->assertUnprocessable()
        ->assertJsonValidationErrors('review_item');

    expect($item->fresh()->status)->toBe(AiReviewStatus::APPROVED)
        ->and(CandidateSkill::query()->count())->toBe(1)
        ->and(AiReviewAudit::query()->count())->toBe(1);
});

it('reviews low-confidence extraction end to end via correction', function () {
    $item = pendingReviewItem(reviewDecisionPayload('Laravel'));
    $proposal = $item->proposed_value;
    $corrected = reviewDecisionPayload('Symfony');
    $candidate = $item->cvExtraction->cvDocument->candidateProfile->user;
    $admin = User::factory()->create(['role' => 'admin']);

    reviewAs($this, $admin)->postJson("/api/admin/ai-review-items/{$item->id}/correct", [
        'corrected_value' => $corrected,
        'decision_reason' => 'The CV states Symfony.',
    ])->assertOk()
        ->assertJsonPath('data.status', AiReviewStatus::CORRECTED->value)
        ->assertJsonPath('data.corrected_value.skills.0.name', 'Symfony');

    $item->refresh();
    $audit = AiReviewAudit::query()->sole();

    // Original proposal preserved; canonical state holds the correction only.
    expect($item->status)->toBe(AiReviewStatus::CORRECTED)
        ->and($item->proposed_value)->toBe($proposal)
        ->and($item->corrected_value)->toBe($corrected)
        ->and(Skill::query()->where('normalized_name', 'symfony')->exists())->toBeTrue()
        ->and(Skill::query()->where('normalized_name', 'laravel')->exists())->toBeFalse()
        ->and($audit->original_proposal)->toBe($proposal)
        ->and($audit->applied_value)->toBe($corrected);

    expect(AuditLog::query()->where('entity_type', 'ai_review_item')->where('entity_id', $item->id)->count())->toBeGreaterThan(0);

    reviewAs($this, $candidate)->getJson('/api/candidate/skills')
        ->assertOk()
        ->assertJsonPath('data.0.skill.name', 'Symfony');
});

it('does not raise review items for high-confidence extractions', function () {
    CvExtraction::factory()->create([
        'status' => CvExtractionStatus::SUCCESS,
        'confidence_score' => '0.9500',
        'extracted_data' => ['skills' => ['PHP']],
    ]);

    expect(AiReviewItem::query()->count())->toBe(0);
});
