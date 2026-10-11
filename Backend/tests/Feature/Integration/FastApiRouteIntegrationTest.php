<?php

use App\Enums\CvExtractionStatus;
use App\Enums\CvParsingStatus;
use App\Models\CandidateProfile;
use App\Models\CvDocument;
use App\Models\JobMatch;
use App\Models\JobPost;
use App\Models\JobSkill;
use App\Services\Ai\JobRequirementService;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function (): void {
    config()->set([
        'services.ai.url' => 'https://ai.test',
        'services.ai.key' => 'test-api-key',
        'services.ai.timeout' => 7,
        'services.ai.connect_timeout' => 2,
    ]);

    Storage::fake('local');
    Http::preventStrayRequests();
});

it('completes CV upload extraction through the Laravel route and FastAPI client', function (): void {
    $profile = CandidateProfile::factory()->create();
    $contents = 'Synthetic QA resume with no personal data. Backend engineering experience.';
    $file = UploadedFile::fake()->createWithContent('synthetic-qa-resume.pdf', $contents);
    $payload = [
        'candidate_id' => (string) $profile->user_id,
        'candidate_profile' => [
            'headline' => 'Synthetic QA Backend Engineer',
            'bio' => null,
        ],
        'educations' => [],
        'experiences' => [],
        'projects' => [],
        'certificates' => [],
        'languages' => [],
        'candidate_skills' => [],
        'target_roles' => ['Backend Engineer'],
        'preferences' => null,
    ];

    Http::fake([
        'ai.test/api/v1/cv/extract-file' => Http::response($payload),
    ]);

    $response = $this->withToken(JWTAuth::fromUser($profile->user))
        ->post('/api/ai/cv/upload', ['cv' => $file]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.extraction.extracted_data', $payload)
        ->assertJsonPath('data.confidence', null);

    Http::assertSent(function (Request $request) use ($contents, $profile): bool {
        $data = $request->data();

        return $request->url() === 'https://ai.test/api/v1/cv/extract-file'
            && $request->hasHeader('X-API-Key', 'test-api-key')
            && $request->hasFile('file', $contents, 'synthetic-qa-resume.pdf')
            && collect($data)->contains(fn (mixed $part): bool => is_array($part)
                && ($part['name'] ?? null) === 'candidate_id'
                && ($part['contents'] ?? null) === (string) $profile->user_id);
    });

    $document = CvDocument::query()->sole();
    expect($document->status)->toBe(CvParsingStatus::COMPLETED)
        ->and($document->extractions()->sole()->status)->toBe(CvExtractionStatus::SUCCESS);
});

it('completes a match report through the Laravel route and FastAPI client', function (): void {
    $profile = CandidateProfile::factory()->create();
    $job = JobPost::factory()->create([
        'title' => 'Synthetic Backend QA Role',
        'description' => 'Build and maintain reliable Laravel APIs backed by PostgreSQL for a synthetic QA role.',
        'canonical_role' => 'Backend Engineer',
        'min_years_experience' => 2,
        'work_mode' => 'remote',
        'employment_type' => 'full_time',
    ]);
    $payload = fastApiRouteMatchPayload($job, $profile);

    Http::fake([
        'ai.test/api/v1/matches/analyze' => Http::response($payload),
    ]);

    $response = $this->withToken(JWTAuth::fromUser($profile->user))
        ->getJson('/api/ai/jobs/'.$job->id.'/match-report');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.job_post_id', $job->id)
        ->assertJsonPath('data.candidate_profile_id', $profile->id);

    Http::assertSent(function (Request $request) use ($job, $profile): bool {
        $data = $request->data();

        return $request->url() === 'https://ai.test/api/v1/matches/analyze'
            && $request->hasHeader('X-API-Key', 'test-api-key')
            && $data['job_id'] === (string) $job->id
            && $data['candidate_id'] === (string) $profile->user_id
            && $data['job_requirements']['role_title'] === $job->title
            && $data['candidate_profile']['candidate_id'] === (string) $profile->user_id
            && ! array_key_exists('metadata', $data)
            && ! array_key_exists('payload', $data);
    });

    expect(JobMatch::query()->where('job_post_id', $job->id)->sole()->score)->toBe('84.50');
});

it('normalizes job requirements from the confirmed FastAPI contract', function (): void {
    $job = JobPost::factory()->create([
        'description' => 'Synthetic QA job description requiring PHP and Laravel API development experience.',
    ]);
    $payload = [
        'job_id' => null,
        'profile' => [
            'role_family' => 'Engineering',
            'seniority' => 'Senior',
            'canonical_role' => 'Backend Engineer',
            'required_skills' => [[
                'skill_id' => null,
                'canonical_name' => 'PHP',
                'category' => 'Programming Languages',
                'raw_extracted' => 'PHP',
                'importance' => 'critical',
                'required_level' => 'advanced',
            ]],
            'preferred_skills' => [[
                'skill_id' => null,
                'canonical_name' => 'Docker',
                'category' => 'Cloud',
                'raw_extracted' => 'Docker',
                'importance' => 'nice_to_have',
                'required_level' => 'intermediate',
            ]],
            'responsibilities' => ['Build reliable APIs.'],
            'min_years_experience' => 2,
            'max_years_experience' => 5,
            'constraints' => ['Remote'],
            'extraction_confidence' => 0.9,
        ],
        'persisted' => false,
    ];

    Http::fake([
        'ai.test/api/v1/jobs/analyze' => Http::response($payload),
    ]);

    app(JobRequirementService::class)->extractAndNormalizeRequirements($job);

    Http::assertSent(function (Request $request) use ($job): bool {
        return $request->url() === 'https://ai.test/api/v1/jobs/analyze'
            && $request->hasHeader('X-API-Key', 'test-api-key')
            && $request->data() === ['job_description' => $job->description];
    });

    $job->refresh();
    expect($job->canonical_role)->toBe('Backend Engineer')
        ->and($job->experience_level)->toBe('Senior')
        ->and($job->min_years_experience)->toBe(2)
        ->and($job->max_years_experience)->toBe(5)
        ->and($job->responsibilities)->toBe(['Build reliable APIs.']);

    $skills = JobSkill::query()->where('job_post_id', $job->id)->with('skill')->get();
    expect($skills)->toHaveCount(2)
        ->and($skills->firstWhere('is_required', true)->skill->name)->toBe('PHP')
        ->and($skills->firstWhere('is_required', false)->skill->name)->toBe('Docker');
});

it('maps FastAPI authentication, validation, and server failures to safe Laravel validation errors', function (int $status, array $body, string $message): void {
    $profile = CandidateProfile::factory()->create();
    $job = JobPost::factory()->create();

    Http::fake([
        'ai.test/api/v1/matches/analyze' => Http::response($body, $status),
    ]);

    $response = $this->withToken(JWTAuth::fromUser($profile->user))
        ->getJson('/api/ai/jobs/'.$job->id.'/match-report');

    $response->assertUnprocessable()
        ->assertJsonPath('errors.ai.0', $message);

    expect(JobMatch::query()->count())->toBe(0)
        ->and($response->json('errors.ai.0'))->not->toContain('Not authenticated');
})->with([
    'authentication failure' => [401, ['detail' => 'Not authenticated'], 'The AI service could not process the request.'],
    'validation error' => [422, ['detail' => 'Invalid synthetic request', 'error_code' => 'VALIDATION_ERROR'], 'The AI service could not process the request.'],
    'upstream server failure' => [503, ['detail' => 'Synthetic service unavailable', 'error_code' => 'SERVICE_UNAVAILABLE'], 'The AI service could not be reached.'],
]);

it('maps an upstream timeout to a safe Laravel connection validation error', function (): void {
    $profile = CandidateProfile::factory()->create();
    $job = JobPost::factory()->create();

    Http::fake([
        'ai.test/api/v1/matches/analyze' => Http::failedConnection('Synthetic timeout'),
    ]);

    $response = $this->withToken(JWTAuth::fromUser($profile->user))
        ->getJson('/api/ai/jobs/'.$job->id.'/match-report');

    $response->assertUnprocessable()
        ->assertJsonPath('errors.ai.0', __('ai.connection_failed'));

    expect(JobMatch::query()->count())->toBe(0);
});

it('maps invalid upstream JSON to a safe Laravel response validation error', function (): void {
    $profile = CandidateProfile::factory()->create();
    $job = JobPost::factory()->create();

    Http::fake([
        'ai.test/api/v1/matches/analyze' => Http::response('not-json', 200, ['Content-Type' => 'text/plain']),
    ]);

    $response = $this->withToken(JWTAuth::fromUser($profile->user))
        ->getJson('/api/ai/jobs/'.$job->id.'/match-report');

    $response->assertUnprocessable()
        ->assertJsonPath('errors.ai.0', __('ai.invalid_response'));

    expect(JobMatch::query()->count())->toBe(0);
});

/**
 * @return array<string, mixed>
 */
function fastApiRouteMatchPayload(JobPost $job, CandidateProfile $profile): array
{
    return [
        'job_id' => (string) $job->id,
        'candidate_id' => (string) $profile->user_id,
        'overall_match_score' => 84.5,
        'qualification_status' => 'Qualified',
        'full_candidate_summary' => 'Synthetic candidate satisfies the synthetic role requirements.',
        'skill_breakdown' => [[
            'skill_name' => 'PHP',
            'required_proficiency' => 'advanced',
            'candidate_proficiency' => 'advanced',
            'match_score' => 90,
            'is_matched' => true,
            'skill_feedback' => 'Strong synthetic match.',
            'evidence_found' => 'Synthetic test evidence only.',
            'resources' => [],
        ]],
        'missing_critical_skills' => [],
        'recommended_upskilling_path' => [],
        'preferred_skills_breakdown' => [],
        'weak_skills' => [],
        'blockers' => [],
        'nice_to_have_gaps' => [],
        'role_alignment_score' => 90,
        'experience_score' => 80,
        'preference_fit_score' => 85,
        'skills_match_score' => 90,
        'score_breakdown' => ['skills' => 90],
        'rationale' => 'Synthetic QA rationale.',
        'priority' => 'high',
    ];
}
