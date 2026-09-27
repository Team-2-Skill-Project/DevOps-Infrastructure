<?php

use App\Models\CandidateProfile;
use App\Models\CvDocument;
use App\Models\CvExtraction;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;

test('a candidate profile can have multiple cv versions', function () {
    $profile = CandidateProfile::factory()->create();

    $cv1 = CvDocument::factory()->create([
        'candidate_profile_id' => $profile->id,
        'version' => 1,
        'is_current' => false,
    ]);

    $cv2 = CvDocument::factory()->create([
        'candidate_profile_id' => $profile->id,
        'version' => 2,
        'is_current' => true,
    ]);

    expect($profile->cvDocuments)
        ->toHaveCount(2);

    expect($cv1->candidateProfile->is($profile))
        ->toBeTrue();

    expect($cv2->candidateProfile->is($profile))
        ->toBeTrue();
});

test('a cv document can have multiple extraction attempts', function () {
    $cv = CvDocument::factory()->create();

    $failedAttempt = CvExtraction::factory()->create([
        'cv_document_id' => $cv->id,
        'attempt_number' => 1,
        'status' => 'failed',
        'error_message' => 'Parser failed',
    ]);

    $successfulAttempt = CvExtraction::factory()->create([
        'cv_document_id' => $cv->id,
        'attempt_number' => 2,
        'status' => 'completed',
        'extracted_data' => [
            'skills' => ['PHP', 'Laravel'],
        ],
        'confidence_score' => '0.9500',
        'completed_at' => now(),
    ]);

    expect($cv->extractions)
        ->toHaveCount(2);

    expect($failedAttempt->cvDocument->is($cv))
        ->toBeTrue();

    expect($successfulAttempt->cvDocument->is($cv))
        ->toBeTrue();

    expect($successfulAttempt->extracted_data)
        ->toBeArray()
        ->and($successfulAttempt->extracted_data['skills'])
        ->toBe(['PHP', 'Laravel']);
});

test('duplicate cv version for the same candidate profile is rejected', function () {
    $profile = CandidateProfile::factory()->create();

    CvDocument::factory()->create([
        'candidate_profile_id' => $profile->id,
        'version' => 1,
    ]);

    expect(fn () => CvDocument::factory()->create([
        'candidate_profile_id' => $profile->id,
        'version' => 1,
    ]))->toThrow(QueryException::class);
});

test('duplicate extraction attempt for the same cv is rejected', function () {
    $cv = CvDocument::factory()->create();

    CvExtraction::factory()->create([
        'cv_document_id' => $cv->id,
        'attempt_number' => 1,
    ]);

    expect(fn () => CvExtraction::factory()->create([
        'cv_document_id' => $cv->id,
        'attempt_number' => 1,
    ]))->toThrow(QueryException::class);
});

test('deleting a candidate profile deletes its cv documents and extractions', function () {
    $profile = CandidateProfile::factory()->create();

    $cv = CvDocument::factory()->create([
        'candidate_profile_id' => $profile->id,
    ]);

    $extraction = CvExtraction::factory()->create([
        'cv_document_id' => $cv->id,
    ]);

    $profile->delete();

    $this->assertDatabaseMissing('cv_documents', [
        'id' => $cv->id,
    ]);

    $this->assertDatabaseMissing('cv_extractions', [
        'id' => $extraction->id,
    ]);
});

test('cv document casts are applied correctly', function () {
    $this->freezeSecond();
    $cv = CvDocument::factory()->create([
        'file_size' => '50000',
        'version' => '2',
        'is_current' => 1,
        'processed_at' => now(),
    ])->fresh();

    expect($cv->file_size)
        ->toBe(50000)
        ->and($cv->version)
        ->toBe(2)
        ->and($cv->is_current)
        ->toBeTrue()
        ->and($cv->processed_at)
        ->toBeInstanceOf(CarbonInterface::class);

    $extraction = CvExtraction::factory()->create([
        'attempt_number' => '1',
        'extracted_data' => ['skills' => ['PHP', 'Laravel']],
        'confidence_score' => 0.95,
        'started_at' => now()->subMinute(),
        'completed_at' => now(),
    ])->fresh();

    expect($extraction->attempt_number)
        ->toBe(1)
        ->and($extraction->extracted_data)
        ->toBe(['skills' => ['PHP', 'Laravel']])
        ->and($extraction->confidence_score)
        ->toBe('0.9500')
        ->and($extraction->started_at)
        ->toBeInstanceOf(CarbonInterface::class)
        ->and($extraction->completed_at)
        ->toBeInstanceOf(CarbonInterface::class);
});
