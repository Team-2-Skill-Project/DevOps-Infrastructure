<?php

use App\Services\Ai\FastApiAiClient;

const FAST_API_LIVE_BASE_URL = 'https://ai-serv5.fastapicloud.dev';

beforeEach(function (): void {
    if (filter_var((string) getenv('RUN_LIVE_AI_TESTS'), FILTER_VALIDATE_BOOL) !== true) {
        $this->markTestSkipped('Set RUN_LIVE_AI_TESTS=true to explicitly run live AI integration tests.');
    }

    if (rtrim((string) config('services.ai.url'), '/') !== FAST_API_LIVE_BASE_URL) {
        $this->markTestSkipped('Live tests only target the confirmed FastAPI deployment.');
    }
});

it('extracts a synthetic text CV profile from the deployed FastAPI service', function (): void {
    $response = app(FastApiAiClient::class)->postMultipart(
        '/api/v1/cv/extract-file',
        'file',
        "Synthetic QA Candidate\nBackend Engineer\nSkills: PHP, Laravel, PostgreSQL.\nNo personal information.",
        'skillmatch-live-test.txt',
        'text/plain',
        ['candidate_id' => 'live-test-candidate'],
    );

    expect($response['candidate_id'] ?? null)->toBe('live-test-candidate')
        ->and($response['candidate_profile'] ?? null)->toBeArray();
});

it('analyzes a synthetic job description without requesting persistence', function (): void {
    $response = app(FastApiAiClient::class)->postJson('/api/v1/jobs/analyze', [
        'job_description' => 'Synthetic QA backend role requiring PHP, Laravel, PostgreSQL, and API testing experience.',
    ]);

    expect($response['profile'] ?? null)->toBeArray()
        ->and($response['persisted'] ?? null)->toBeFalse();
});

it('analyzes a synthetic candidate match against a synthetic role', function (): void {
    $response = app(FastApiAiClient::class)->postJson('/api/v1/matches/analyze', [
        'job_id' => 'live-test-job',
        'candidate_id' => 'live-test-candidate',
        'job_requirements' => [
            'job_id' => 'live-test-job',
            'role_title' => 'Synthetic Backend QA Role',
            'skills' => [[
                'skill_name' => 'PHP',
                'proficiency' => 'Intermediate',
                'is_critical' => true,
            ]],
            'summary' => 'Synthetic role requirements with no personal data.',
        ],
        'candidate_profile' => [
            'candidate_id' => 'live-test-candidate',
            'name' => null,
            'skills' => [[
                'skill_name' => 'PHP',
                'proficiency' => 'Intermediate',
            ]],
            'work_history' => [],
            'projects' => [],
            'target_roles' => ['Backend Engineer'],
            'preferences' => ['work_mode' => 'remote'],
        ],
        'preferred_skills' => [],
        'job_title' => 'Synthetic Backend QA Role',
        'canonical_role' => 'Backend Engineer',
        'min_years_experience' => 1,
        'work_mode' => 'remote',
        'location' => null,
        'employment_type' => 'full_time',
    ]);

    expect($response['job_id'] ?? null)->toBe('live-test-job')
        ->and($response['candidate_id'] ?? null)->toBe('live-test-candidate')
        ->and($response['overall_match_score'] ?? null)->toBeNumeric()
        ->and($response['qualification_status'] ?? null)->toBeString();
});
