<?php

use App\Enums\AiReviewAction;
use App\Models\AiReviewAudit;
use App\Models\AiReviewItem;
use App\Models\AuditLog;
use App\Models\CvExtraction;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

function auditIntegrationPayload(): array
{
    return [
        'skills' => [[
            'name' => 'Laravel',
            'category' => 'Framework',
            'proficiency_level' => 'advanced',
            'confidence_score' => 0.61,
            'evidence' => ['Built production APIs'],
        ]],
        'experiences' => [[
            'company_name' => 'Acme',
            'title' => 'Backend Engineer',
            'start_date' => '2024-01-01',
            'technologies' => ['Laravel'],
        ]],
    ];
}

function auditIntegrationItem(): AiReviewItem
{
    CvExtraction::factory()->create([
        'confidence_score' => '0.6100',
        'extracted_data' => auditIntegrationPayload(),
    ]);

    return AiReviewItem::query()->sole();
}

it('exposes approve decisions in the unified audit query without touching domain audit', function () {
    $item = auditIntegrationItem();
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($admin))->postJson("/api/admin/ai-review-items/{$item->id}/approve", [
        'decision_reason' => 'Confirmed against the source CV.',
    ])->assertOk();

    $domain = AiReviewAudit::query()->sole();
    expect($domain->action)->toBe(AiReviewAction::APPROVED)
        ->and($domain->applied_value)->toBe($item->refresh()->proposed_value)
        ->and($domain->reviewer_id)->toBe($admin->id);

    $log = AuditLog::query()->where('action', 'ai_review_approved')->sole();
    expect($log->entity_type->value)->toBe('ai_review_item')
        ->and($log->entity_id)->toBe($item->id)
        ->and($log->source->value)->toBe('ai_review')
        ->and($log->actor->is($admin))->toBeTrue()
        ->and($log->before)->toBe(['status' => 'pending'])
        ->and($log->after)->toBe(['status' => 'approved'])
        ->and($log->metadata['entity_type'])->toBe('cv_extraction')
        ->and($log->metadata['operation'])->toBe('cv_profile_sync')
        ->and($log->metadata['decision_reason'])->toBe('Confirmed against the source CV.');
});

it('exposes correct decisions in the unified audit query', function () {
    $item = auditIntegrationItem();
    $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
    $corrected = auditIntegrationPayload();
    $corrected['skills'][0]['proficiency_level'] = 'expert';

    $this->withToken(JWTAuth::fromUser($admin))->postJson("/api/admin/ai-review-items/{$item->id}/correct", [
        'corrected_value' => $corrected,
        'decision_reason' => 'Fixed proficiency.',
    ])->assertOk();

    expect(AiReviewAudit::query()->sole()->action)->toBe(AiReviewAction::CORRECTED);

    $log = AuditLog::query()->where('action', 'ai_review_corrected')->sole();
    expect($log->entity_id)->toBe($item->id)
        ->and($log->after)->toBe(['status' => 'corrected'])
        ->and($log->actor->is($admin))->toBeTrue();
});

it('exposes reject decisions in the unified audit query', function () {
    $item = auditIntegrationItem();
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($admin))->postJson("/api/admin/ai-review-items/{$item->id}/reject", [
        'decision_reason' => 'Hallucinated entries.',
    ])->assertOk();

    $domain = AiReviewAudit::query()->sole();
    expect($domain->action)->toBe(AiReviewAction::REJECTED)->and($domain->applied_value)->toBeNull();

    $log = AuditLog::query()->where('action', 'ai_review_rejected')->sole();
    expect($log->entity_id)->toBe($item->id)->and($log->after)->toBe(['status' => 'rejected']);
});

it('creates no generic audit when the decision is rejected by guards', function () {
    $item = auditIntegrationItem();
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($admin))->postJson("/api/admin/ai-review-items/{$item->id}/reject", [
        'decision_reason' => 'First rejection.',
    ])->assertOk();
    $this->withToken(JWTAuth::fromUser($admin))->postJson("/api/admin/ai-review-items/{$item->id}/approve", [])
        ->assertUnprocessable();

    expect(AiReviewAudit::query()->count())->toBe(1)
        ->and(AuditLog::query()->where('entity_type', 'ai_review_item')->count())->toBe(1);
});
