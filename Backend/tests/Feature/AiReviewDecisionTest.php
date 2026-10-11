<?php

use App\Enums\AiReviewStatus;
use App\Models\AiReviewAudit;
use App\Models\AiReviewItem;
use App\Models\CandidateSkill;
use App\Models\CvExtraction;
use App\Models\Experience;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

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

function pendingCvReviewItem(array $payload = []): AiReviewItem
{
    CvExtraction::factory()->create([
        'confidence_score' => '0.6100',
        'extracted_data' => $payload === [] ? reviewDecisionPayload() : $payload,
    ]);

    return AiReviewItem::query()->sole();
}

it('approves a pending proposal and atomically writes canonical data reviewer metadata and audit', function () {
    $item = pendingCvReviewItem();
    $proposal = $item->proposed_value;
    $admin = User::factory()->create(['role' => 'admin']);

    $this->withToken(JWTAuth::fromUser($admin))
        ->postJson("/api/admin/ai-review-items/{$item->id}/approve", [
            'decision_reason' => 'Confirmed against the source CV.',
            'reviewer_notes' => 'Terminology is consistent.',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'approved')
        ->assertJsonPath('data.reviewer.id', $admin->id);

    $item->refresh();
    $skill = Skill::query()->where('normalized_name', 'laravel')->sole();
    $candidateSkill = CandidateSkill::query()->sole();
    $experience = Experience::query()->sole();
    $audit = AiReviewAudit::query()->sole();

    expect($item->status)->toBe(AiReviewStatus::APPROVED)
        ->and($item->proposed_value)->toBe($proposal)
        ->and($item->reviewer_id)->toBe($admin->id)
        ->and($item->reviewed_at)->not->toBeNull()
        ->and($item->decision_reason)->toBe('Confirmed against the source CV.')
        ->and($item->reviewer_notes)->toBe('Terminology is consistent.')
        ->and($candidateSkill->skill_id)->toBe($skill->id)
        ->and($candidateSkill->source)->toBe('cv_extracted')
        ->and($candidateSkill->confidence)->toBe('0.61')
        ->and($experience->job_title)->toBe('Backend Engineer')
        ->and($experience->source)->toBe('cv_extracted')
        ->and($audit->action->value)->toBe('approved')
        ->and($audit->original_proposal)->toBe($proposal)
        ->and($audit->reviewer_id)->toBe($admin->id)
        ->and($audit->applied_value)->toBe($proposal)
        ->and($item->cvExtraction->fresh()->extracted_data['verified_payload'])->toBe($proposal);
});

it('applies a strictly validated correction while retaining the original AI proposal', function () {
    $item = pendingCvReviewItem(reviewDecisionPayload('Laravel'));
    $proposal = $item->proposed_value;
    $corrected = reviewDecisionPayload('Symfony');
    $admin = User::factory()->create(['role' => 'admin']);

    $this->withToken(JWTAuth::fromUser($admin))
        ->postJson("/api/admin/ai-review-items/{$item->id}/correct", [
            'corrected_value' => $corrected,
            'decision_reason' => 'The CV states Symfony.',
            'reviewer_notes' => 'Corrected the framework name.',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'corrected')
        ->assertJsonPath('data.corrected_value.skills.0.name', 'Symfony');

    $item->refresh();
    $audit = AiReviewAudit::query()->sole();

    expect($item->status)->toBe(AiReviewStatus::CORRECTED)
        ->and($item->proposed_value)->toBe($proposal)
        ->and($item->corrected_value)->toBe($corrected)
        ->and(Skill::query()->where('normalized_name', 'symfony')->exists())->toBeTrue()
        ->and(Skill::query()->where('normalized_name', 'laravel')->exists())->toBeFalse()
        ->and($audit->original_proposal)->toBe($proposal)
        ->and($audit->applied_value)->toBe($corrected);
});

it('rejects a proposal without mutating canonical candidate data', function () {
    $item = pendingCvReviewItem();
    $proposal = $item->proposed_value;
    $originalExtraction = $item->cvExtraction->extracted_data;
    $admin = User::factory()->create(['role' => 'admin']);

    $this->withToken(JWTAuth::fromUser($admin))
        ->postJson("/api/admin/ai-review-items/{$item->id}/reject", [
            'decision_reason' => 'The extraction is not supported by the CV.',
            'reviewer_notes' => 'No matching evidence.',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'rejected');

    $item->refresh();
    $audit = AiReviewAudit::query()->sole();

    expect($item->status)->toBe(AiReviewStatus::REJECTED)
        ->and($item->proposed_value)->toBe($proposal)
        ->and($item->decision_reason)->toBe('The extraction is not supported by the CV.')
        ->and(CandidateSkill::query()->count())->toBe(0)
        ->and(Experience::query()->count())->toBe(0)
        ->and($item->cvExtraction->fresh()->extracted_data)->toBe($originalExtraction)
        ->and($audit->action->value)->toBe('rejected')
        ->and($audit->applied_value)->toBeNull();
});

it('rejects malformed corrections and unexpected nested keys', function (array $correctedValue, string $error) {
    $item = pendingCvReviewItem();
    $admin = User::factory()->create(['role' => 'admin']);

    $this->withToken(JWTAuth::fromUser($admin))
        ->postJson("/api/admin/ai-review-items/{$item->id}/correct", [
            'corrected_value' => $correctedValue,
            'decision_reason' => 'Correction required.',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors($error);

    expect($item->fresh()->status)->toBe(AiReviewStatus::PENDING)
        ->and(AiReviewAudit::query()->count())->toBe(0)
        ->and(CandidateSkill::query()->count())->toBe(0);
})->with([
    'missing skill name' => [['skills' => [['category' => 'Framework']]], 'corrected_value.skills.0.name'],
    'unknown item key' => [['skills' => [['name' => 'Laravel', 'is_admin' => true]]], 'corrected_value.skills.0'],
    'unknown root key' => [['skills' => [], 'secret' => 'value'], 'corrected_value'],
]);

it('prevents all decisions after a terminal review state', function (AiReviewStatus $status, string $action, array $payload) {
    $item = AiReviewItem::factory()->create(['status' => $status]);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->withToken(JWTAuth::fromUser($admin))
        ->postJson("/api/admin/ai-review-items/{$item->id}/{$action}", $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('review_item');

    expect($item->fresh()->status)->toBe($status)
        ->and(AiReviewAudit::query()->count())->toBe(0);
})->with([
    'approved cannot approve' => [AiReviewStatus::APPROVED, 'approve', []],
    'rejected cannot approve' => [AiReviewStatus::REJECTED, 'approve', []],
    'corrected cannot reject' => [AiReviewStatus::CORRECTED, 'reject', ['decision_reason' => 'No longer valid.']],
]);

it('rolls back canonical and review changes when canonical persistence fails', function () {
    $item = pendingCvReviewItem();
    $admin = User::factory()->create(['role' => 'admin']);
    DB::statement("CREATE TRIGGER fail_candidate_skill BEFORE INSERT ON candidate_skills BEGIN SELECT RAISE(FAIL, 'forced failure'); END");

    try {
        $this->withToken(JWTAuth::fromUser($admin))
            ->postJson("/api/admin/ai-review-items/{$item->id}/approve")
            ->assertServerError();
    } finally {
        DB::statement('DROP TRIGGER fail_candidate_skill');
    }

    expect($item->fresh()->status)->toBe(AiReviewStatus::PENDING)
        ->and($item->reviewer_id)->toBeNull()
        ->and(AiReviewAudit::query()->count())->toBe(0)
        ->and(Skill::query()->count())->toBe(0)
        ->and(CandidateSkill::query()->count())->toBe(0)
        ->and(Experience::query()->count())->toBe(0);
});
