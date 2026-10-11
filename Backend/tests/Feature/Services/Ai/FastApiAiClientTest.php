<?php

use App\Exceptions\AiServiceException;
use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\CvDocument;
use App\Models\JobPost;
use App\Models\JobSkill;
use App\Models\Skill;
use App\Services\Ai\CvExtractionService;
use App\Services\Ai\FastApiAiClient;
use App\Services\Ai\JobMatchingService;
use App\Services\Ai\JobRequirementService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    config()->set([
        'services.ai.url' => 'https://ai.test',
        'services.ai.key' => 'test-api-key',
        'services.ai.timeout' => 7,
        'services.ai.connect_timeout' => 2,
    ]);
});

it('persists a CV extraction returned by the confirmed FastAPI endpoint', function () {
    Storage::fake('local');
    $profile = CandidateProfile::factory()->create();
    $path = 'cvs/'.$profile->id.'/resume.pdf';
    Storage::disk('local')->put($path, 'private cv contents');
    $document = CvDocument::factory()->create([
        'candidate_profile_id' => $profile->id,
        'storage_disk' => 'local',
        'storage_path' => $path,
        'original_filename' => 'resume.pdf',
        'mime_type' => 'application/pdf',
    ]);
    $payload = [
        'candidate_id' => (string) $profile->user_id,
        'candidate_profile' => ['headline' => 'Backend Engineer'],
        'candidate_skills' => [['skill_name' => 'Laravel']],
    ];

    Http::preventStrayRequests();
    Http::fake([
        'ai.test/api/v1/cv/extract-file' => Http::response($payload),
    ]);

    $result = app(CvExtractionService::class)->extractAndPersist($document);

    Http::assertSent(function (Request $request) use ($profile): bool {
        $data = $request->data();

        return $request->url() === 'https://ai.test/api/v1/cv/extract-file'
            && $request->hasHeader('X-API-Key', 'test-api-key')
            && $request->hasFile('file', 'private cv contents', 'resume.pdf')
            && collect($data)->contains(fn (mixed $part): bool => is_array($part)
                && ($part['name'] ?? null) === 'candidate_id'
                && ($part['contents'] ?? null) === (string) $profile->user_id);
    });
    expect($result['extraction']->fresh()->extracted_data)->toBe($payload)
        ->and($document->fresh()->status->value)->toBe('completed')
        ->and($result['confidence'])->toBeNull();
});

it('maps the confirmed match response into the existing job match record', function () {
    $profile = CandidateProfile::factory()->create();
    $skill = Skill::factory()->create(['name' => 'Laravel', 'category' => 'Framework']);
    CandidateSkill::factory()->for($profile)->for($skill)->create(['proficiency_level' => 'advanced']);
    $job = JobPost::factory()->create([
        'title' => 'Backend Engineer',
        'description' => str_repeat('Build reliable APIs with Laravel and PostgreSQL. ', 3),
        'canonical_role' => 'Backend Engineer',
        'min_years_experience' => 2,
        'work_mode' => 'remote',
        'employment_type' => 'full_time',
    ]);
    JobSkill::factory()->for($job)->for($skill)->create([
        'is_required' => true,
        'required_level' => 'advanced',
    ]);
    $payload = [
        'job_id' => (string) $job->id,
        'candidate_id' => (string) $profile->user_id,
        'overall_match_score' => 84.5,
        'qualification_status' => 'Qualified',
        'full_candidate_summary' => 'The candidate meets the core requirements.',
        'skill_breakdown' => [['skill_name' => 'Laravel', 'score' => 90]],
        'missing_critical_skills' => [],
        'nice_to_have_gaps' => ['Docker'],
        'weak_skills' => [],
        'rationale' => 'Strong framework experience.',
        'priority' => 'high',
    ];

    Http::preventStrayRequests();
    Http::fake([
        'ai.test/api/v1/matches/analyze' => Http::response($payload),
    ]);

    $match = app(JobMatchingService::class)->calculateAndPersistMatch($profile, $job);

    Http::assertSent(function (Request $request) use ($job, $profile): bool {
        $data = $request->data();

        return $request->url() === 'https://ai.test/api/v1/matches/analyze'
            && $request->hasHeader('X-API-Key', 'test-api-key')
            && $data['job_id'] === (string) $job->id
            && $data['candidate_id'] === (string) $profile->user_id
            && $data['job_requirements']['skills'][0]['skill_name'] === 'Laravel'
            && ! array_key_exists('metadata', $data)
            && ! array_key_exists('payload', $data);
    });
    expect((float) $match->score)->toBe(84.5)
        ->and($match->missing_skills)->toBe(['Docker'])
        ->and($match->matched_skills)->toBe($payload['skill_breakdown']);
});

it('converts FastAPI validation errors into the existing Laravel validation response exception', function () {
    $job = JobPost::factory()->create();

    Http::preventStrayRequests();
    Http::fake([
        'ai.test/api/v1/jobs/analyze' => Http::response([
            'detail' => 'The job description is invalid.',
            'error_code' => 'VALIDATION_ERROR',
        ], 422),
    ]);

    expect(fn () => app(JobRequirementService::class)->extractAndNormalizeRequirements($job))
        ->toThrow(ValidationException::class);
    expect($job->fresh()->canonical_role)->toBeNull();
});

it('converts an AI service timeout into a safe client exception', function () {
    Http::preventStrayRequests();
    Http::fake([
        'ai.test/*' => Http::failedConnection('cURL error 28: Operation timed out'),
    ]);

    expect(fn () => app(FastApiAiClient::class)->postJson('/api/v1/matches/analyze', []))
        ->toThrow(AiServiceException::class, 'could not be completed');
});

it('converts AI service failures into the existing Laravel validation exception', function () {
    $profile = CandidateProfile::factory()->create();
    $job = JobPost::factory()->create();

    Http::preventStrayRequests();
    Http::fake([
        'ai.test/api/v1/matches/analyze' => Http::response([
            'detail' => 'The AI provider is unavailable.',
            'error_code' => 'SERVICE_UNAVAILABLE',
        ], 503),
    ]);

    expect(fn () => app(JobMatchingService::class)->calculateAndPersistMatch($profile, $job))
        ->toThrow(ValidationException::class);
    expect($job->jobMatches()->count())->toBe(0);
});

it('omits the API key header when no key is configured', function () {
    config()->set('services.ai.key', null);
    Http::preventStrayRequests();
    Http::fake([
        'ai.test/health' => Http::response(['status' => 'ok']),
    ]);

    app(FastApiAiClient::class)->getJson('/health');

    Http::assertSent(fn (Request $request): bool => ! $request->hasHeader('X-API-Key'));
});

it('rejects an invalid AI service URL without making a request', function () {
    config()->set('services.ai.url', 'not-a-url');
    Http::preventStrayRequests();
    Http::fake();

    expect(fn () => app(FastApiAiClient::class)->getJson('/health'))
        ->toThrow(AiServiceException::class);
    Http::assertNothingSent();
});
