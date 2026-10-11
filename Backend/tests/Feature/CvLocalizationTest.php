<?php

use App\Enums\CvExtractionStatus;
use App\Enums\CvParsingStatus;
use App\Models\CandidateProfile;
use App\Models\CvDocument;
use App\Models\CvExtraction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

test('CV authentication errors use the requested language with English fallback', function (string $language, string $message) {
    $this->withHeader('Accept-Language', $language)
        ->getJson('/api/cv/history')
        ->assertUnauthorized()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic' => ['ar', 'غير مصادق عليه.'],
    'regional Arabic' => ['ar-EG', 'غير مصادق عليه.'],
    'English' => ['en', 'Unauthenticated.'],
    'regional English' => ['en-US', 'Unauthenticated.'],
    'unsupported language' => ['fr-FR', 'Unauthenticated.'],
]);

test('CV uploads return localized messages and preserve filenames and status values', function (string $language, string $message) {
    Storage::fake('local');
    $profile = CandidateProfile::factory()->create();
    $file = UploadedFile::fake()->create('Senior Backend Engineer CV.pdf', 100, 'application/pdf');

    $this->withToken(JWTAuth::fromUser($profile->user))
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/cv/upload', ['cv' => $file])
        ->assertCreated()
        ->assertJsonPath('message', $message);

    $document = CvDocument::query()->sole();
    expect($document->original_filename)->toBe('Senior Backend Engineer CV.pdf')
        ->and($document->status)->toBe(CvParsingStatus::UPLOADED);
    Storage::disk('local')->assertExists($document->storage_path);
})->with([
    'Arabic' => ['ar', 'تم رفع السيرة الذاتية بنجاح وإضافتها إلى قائمة انتظار المعالجة.'],
    'English' => ['en-US', 'CV uploaded successfully and queued for processing.'],
    'unsupported language' => ['de-DE', 'CV uploaded successfully and queued for processing.'],
]);

test('CV upload validation uses localized attribute names with English fallback', function (string $language, string $message) {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/cv/upload')
        ->assertUnprocessable()
        ->assertJsonPath('errors.cv.0', $message);
})->with([
    'Arabic' => ['ar-EG', 'حقل السيرة الذاتية مطلوب.'],
    'English' => ['en', 'The CV field is required.'],
    'unsupported language' => ['es', 'The CV field is required.'],
]);

test('CV extraction validation uses Arabic attribute names', function () {
    $extraction = CvExtraction::factory()->create();

    $this->withToken(JWTAuth::fromUser($extraction->cvDocument->candidateProfile->user))
        ->withHeader('Accept-Language', 'ar')
        ->postJson('/api/cv/extractions/'.$extraction->id.'/verify', ['skills' => 'invalid'])
        ->assertUnprocessable()
        ->assertJsonPath('errors.skills.0', 'يجب أن يكون حقل المهارات مصفوفة.');
});

test('CV ownership errors are localized without exposing another candidates document', function (string $language, string $message) {
    $document = CvDocument::factory()->create();
    $otherCandidate = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($otherCandidate->user))
        ->withHeader('Accept-Language', $language)
        ->getJson('/api/cv/status/'.$document->id)
        ->assertForbidden()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic' => ['ar', 'غير مصرح لك بالوصول إلى هذه السيرة الذاتية.'],
    'English' => ['en-US', 'You are not authorized to access this CV.'],
    'unsupported language' => ['it', 'You are not authorized to access this CV.'],
]);

test('missing CV resources return localized not-found messages', function (string $endpoint, string $language, string $message) {
    $profile = CandidateProfile::factory()->create();

    $this->withToken(JWTAuth::fromUser($profile->user))
        ->withHeader('Accept-Language', $language)
        ->postJson($endpoint)
        ->assertNotFound()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic document' => ['/api/cv/retry/999999', 'ar-EG', 'لم يتم العثور على السيرة الذاتية.'],
    'English extraction' => ['/api/cv/extractions/999999/verify', 'en', 'CV extraction not found.'],
    'fallback document' => ['/api/cv/retry/999999', 'fr', 'CV document not found.'],
]);

test('CV retry messages are localized without translating the internal status', function () {
    $document = CvDocument::factory()->create(['status' => CvParsingStatus::FAILED]);

    $this->withToken(JWTAuth::fromUser($document->candidateProfile->user))
        ->withHeader('Accept-Language', 'ar')
        ->postJson('/api/cv/retry/'.$document->id)
        ->assertOk()
        ->assertJsonPath('message', 'تمت إعادة محاولة معالجة السيرة الذاتية بنجاح.');

    expect($document->fresh()->status)->toBe(CvParsingStatus::UPLOADED);
});

test('CV verification localizes its response without changing extracted content or enum values', function () {
    $extraction = CvExtraction::factory()->create([
        'status' => CvExtractionStatus::SUCCESS,
        'raw_text' => 'Senior engineer with Laravel experience.',
        'extracted_data' => ['summary' => 'خبرة في تطوير الأنظمة.'],
    ]);

    $this->withToken(JWTAuth::fromUser($extraction->cvDocument->candidateProfile->user))
        ->withHeader('Accept-Language', 'ar-EG')
        ->postJson('/api/cv/extractions/'.$extraction->id.'/verify', ['skills' => [], 'experiences' => []])
        ->assertOk()
        ->assertJsonPath('message', 'تم التحقق من البيانات المستخرجة ومزامنتها مع الملف الشخصي بنجاح.');

    $extraction->refresh();
    expect($extraction->raw_text)->toBe('Senior engineer with Laravel experience.')
        ->and($extraction->status)->toBe(CvExtractionStatus::SUCCESS)
        ->and($extraction->extracted_data)->toBe([
            'summary' => 'خبرة في تطوير الأنظمة.',
            'verified_payload' => ['skills' => [], 'experiences' => []],
        ]);
});
