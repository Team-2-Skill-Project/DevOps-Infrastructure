<?php

use App\Models\CvExtraction;
use App\Models\User;
use App\Services\CvService;

test('cv verification validates input before syncing extracted data', function () {
    $extraction = CvExtraction::factory()->create();
    $originalData = $extraction->extracted_data;
    $this->actingAs($extraction->cvDocument->candidateProfile->user);

    $this->postJson("/api/cv/extractions/{$extraction->id}/verify", ['skills' => 'invalid'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('skills');

    expect($extraction->fresh()->extracted_data)->toBe($originalData);
});

test('cv verification rejects another candidates extraction', function () {
    $extraction = CvExtraction::factory()->create();
    $originalData = $extraction->extracted_data;
    $this->actingAs(User::factory()->create());

    $this->postJson("/api/cv/extractions/{$extraction->id}/verify", [])
        ->assertForbidden();

    expect($extraction->fresh()->extracted_data)->toBe($originalData);
});

test('cv verification preserves extracted data when saving a verified payload', function (?array $extractedData) {
    $extraction = CvExtraction::factory()->create(['extracted_data' => $extractedData]);
    $this->actingAs($extraction->cvDocument->candidateProfile->user);

    $this->postJson("/api/cv/extractions/{$extraction->id}/verify", ['skills' => [], 'experiences' => []])
        ->assertOk()
        ->assertExactJson(['message' => 'Extracted data verified and synced to profile successfully.']);

    expect($extraction->fresh()->extracted_data)->toBe([
        ...($extractedData ?? []),
        'verified_payload' => ['skills' => [], 'experiences' => []],
    ]);
})->with([
    'existing data' => [['parser_field' => 'preserved']],
    'no data' => [null],
]);

test('cv verification rejects scalar extracted data without overwriting it', function () {
    $extraction = CvExtraction::factory()->create(['extracted_data' => 'invalid']);
    $profile = $extraction->cvDocument->candidateProfile;

    expect(fn () => app(CvService::class)->verifyAndSyncExtractedData($profile, $extraction, []))
        ->toThrow(UnexpectedValueException::class, 'CV extracted data must be an array.');

    expect($extraction->fresh()->extracted_data)->toBe('invalid');
});
