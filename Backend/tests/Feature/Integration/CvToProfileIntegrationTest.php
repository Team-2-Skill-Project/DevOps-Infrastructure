<?php

/**
 * Task16 final integration — CV → Profile.
 *
 * Covers the REAL production journey:
 * candidate upload (POST /api/cv/upload) → CvDocument stored →
 * extraction completed (provider boundary simulated with a factory row;
 * no production LLM provider/queue exists, ProcessCvJob is not dispatched) →
 * high-confidence path creates NO admin review item →
 * candidate user-review confirmation (POST /api/cv/extractions/{id}/verify,
 * the confirmation path that ACTUALLY exists) →
 * canonical CandidateSkill/Experience sync → profile + skills APIs reflect it.
 *
 * Classification: A (fully implemented, integration test only).
 */

use App\Enums\CvExtractionStatus;
use App\Enums\CvParsingStatus;
use App\Models\AiReviewItem;
use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\CvDocument;
use App\Models\CvExtraction;
use App\Models\Experience;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
    Storage::fake('local');
});

function integrationUploadCv(object $test, User $user): CvDocument
{
    $file = UploadedFile::fake()->create('Senior Backend Engineer CV.pdf', 100, 'application/pdf');

    $response = integrationAs($test, $user)->postJson('/api/cv/upload', ['cv' => $file]);

    $response->assertCreated()->assertJsonPath('status', 'success');

    return CvDocument::query()->where('candidate_profile_id', $user->candidateProfile->id)->latest('version')->firstOrFail();
}

/**
 * Authenticate follow-up requests as the given user.
 *
 * Tymon's JWTGuard caches the resolved user on the guard singleton, which
 * persists across HTTP calls inside one test (production boots a fresh
 * container per request, so this is test-only). Forgetting guards forces
 * the next request to parse its own bearer token.
 */
function integrationAs(object $test, User $user): object
{
    // Both the guard user and the parsed JWT are cached on container
    // singletons that survive across HTTP calls inside one test.
    auth()->forgetGuards();
    app(JWT::class)->unsetToken();

    return $test->withToken(JWTAuth::fromUser($user));
}

it('integrates upload storage extraction verify and canonical profile sync', function () {
    $profile = CandidateProfile::factory()->create();
    $user = $profile->user;

    // 1. Upload → stored, owned, current, queued status.
    $document = integrationUploadCv($this, $user);

    expect($document->candidate_profile_id)->toBe($profile->id)
        ->and($document->is_current)->toBeTrue()
        ->and($document->status)->toBe(CvParsingStatus::UPLOADED)
        ->and($document->version)->toBe(1);
    Storage::disk('local')->assertExists($document->storage_path);

    // 2. AI extraction completes (provider boundary; no production LLM runner exists).
    $extraction = CvExtraction::factory()->create([
        'cv_document_id' => $document->id,
        'status' => CvExtractionStatus::SUCCESS,
        'confidence_score' => '0.9500',
        'extracted_data' => ['skills' => ['PHP', 'Laravel'], 'experience_years' => 3],
    ]);

    // High-confidence extraction must NOT raise an admin review item.
    expect(AiReviewItem::query()->count())->toBe(0);

    // 3. Candidate user-review confirmation (the confirmation path that exists).
    integrationAs($this, $user)->postJson("/api/cv/extractions/{$extraction->id}/verify", [
        'skills' => [
            ['name' => 'Laravel', 'category' => 'Framework', 'proficiency_level' => 'advanced', 'confidence_score' => 0.9],
        ],
        'experiences' => [
            ['company_name' => 'Acme', 'title' => 'Backend Engineer', 'start_date' => '2023-01-01', 'description' => 'Built APIs.'],
        ],
    ])->assertOk();

    // 4. Canonical state persisted.
    $candidateSkill = CandidateSkill::query()->where('candidate_profile_id', $profile->id)->sole();
    expect($candidateSkill->skill->name)->toBe('Laravel')
        ->and($candidateSkill->source)->toBe('cv_extracted')
        ->and($candidateSkill->proficiency_level)->toBe('advanced')
        ->and((float) $candidateSkill->confidence)->toBe(0.9);

    $experience = Experience::query()->where('candidate_profile_id', $profile->id)->sole();
    expect($experience->job_title)->toBe('Backend Engineer')
        ->and($experience->source)->toBe('cv_extracted');

    expect($extraction->fresh()->extracted_data['verified_payload']['skills'][0]['name'])->toBe('Laravel');

    // 5. Candidate-facing APIs reflect the canonical result.
    integrationAs($this, $user)->getJson('/api/candidate/skills')
        ->assertOk()
        ->assertJsonPath('data.0.skill.name', 'Laravel');

    integrationAs($this, $user)->getJson('/api/candidate/profile')
        ->assertOk()
        ->assertJsonPath('data.profile_exists', true);
});

it('keeps cv profile sync isolated per candidate and idempotent for skills on retry', function () {
    $profile = CandidateProfile::factory()->create();
    $other = CandidateProfile::factory()->create();
    $document = integrationUploadCv($this, $profile->user);
    $extraction = CvExtraction::factory()->create([
        'cv_document_id' => $document->id,
        'status' => CvExtractionStatus::SUCCESS,
        'confidence_score' => '0.9500',
        'extracted_data' => ['skills' => ['PHP']],
    ]);
    $payload = [
        'skills' => [['name' => 'Laravel', 'category' => 'Framework']],
        'experiences' => [],
    ];

    // Another candidate cannot confirm this extraction.
    integrationAs($this, $other->user)
        ->postJson("/api/cv/extractions/{$extraction->id}/verify", $payload)
        ->assertForbidden();

    expect(CandidateSkill::query()->count())->toBe(0);

    // Owner verifies twice: canonical skills must not duplicate.
    integrationAs($this, $profile->user)->postJson("/api/cv/extractions/{$extraction->id}/verify", $payload)->assertOk();
    integrationAs($this, $profile->user)->postJson("/api/cv/extractions/{$extraction->id}/verify", $payload)->assertOk();

    expect(CandidateSkill::query()->where('candidate_profile_id', $profile->id)->count())->toBe(1)
        ->and(CandidateSkill::query()->where('candidate_profile_id', $other->id)->count())->toBe(0);
});

it('replaces the current cv on re-upload while preserving history', function () {
    $profile = CandidateProfile::factory()->create();
    $first = integrationUploadCv($this, $profile->user);
    $second = integrationUploadCv($this, $profile->user);

    expect($second->id)->not->toBe($first->id)
        ->and($second->version)->toBe(2)
        ->and($second->is_current)->toBeTrue()
        ->and($first->fresh()->is_current)->toBeFalse();

    integrationAs($this, $profile->user)->getJson('/api/cv/history')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});
