<?php

use App\Models\Company;
use App\Models\JobPost;
use App\Models\JobSkill;
use App\Models\Skill;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

dataset('job admin endpoints', [
    ['postJson', '/api/admin/jobs'],
    ['patchJson', '/api/admin/jobs/1'],
    ['getJson', '/api/admin/jobs/1'],
]);

it('requires JWT for job admin endpoints', function (string $method, string $url) {
    $this->{$method}($url)->assertUnauthorized();
})->with('job admin endpoints');

it('rejects candidates and inactive administrators', function (string $method, string $url, string $role, bool $active) {
    $user = User::factory()->create(['role' => $role, 'is_active' => $active]);

    $this->withToken(JWTAuth::fromUser($user))->{$method}($url)->assertForbidden();
})->with('job admin endpoints')->with([
    ['candidate', true], ['admin', false], ['super_admin', false],
]);

it('creates a direct job with required and preferred skills', function (string $role) {
    $admin = User::factory()->create(['role' => $role]);
    $company = Company::factory()->create(['is_active' => true]);
    $required = Skill::factory()->create(['name' => 'Laravel']);
    $preferred = Skill::factory()->create(['name' => 'Docker']);

    $response = $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/jobs', [
        'company_id' => $company->id,
        'title' => 'Backend Intern',
        'description' => 'Build reliable services.',
        'job_type' => 'internship',
        'work_mode' => 'hybrid',
        'application_method' => 'internal',
        'min_years_experience' => 0,
        'max_years_experience' => 2,
        'responsibilities' => ['Build APIs'],
        'required_skills' => [['skill_id' => $required->id, 'importance' => 5, 'required_level' => 'advanced']],
        'preferred_skills' => [['skill_id' => $preferred->id, 'importance' => 2, 'required_level' => 'intermediate']],
        'source' => 'spoofed',
        'created_by' => 999999,
    ])->assertCreated()->assertJsonPath('data.job_type', 'internship')
        ->assertJsonPath('data.source', JobPost::SOURCE_DIRECT)
        ->assertJsonPath('data.application_method', 'internal')
        ->assertJsonPath('data.is_active', true)
        ->assertJsonPath('data.required_skills.0.id', $required->id)
        ->assertJsonPath('data.required_skills.0.importance', 5)
        ->assertJsonPath('data.preferred_skills.0.id', $preferred->id);

    $job = JobPost::findOrFail($response->json('data.id'));
    expect($job->created_by)->toBe($admin->id)
        ->and($job->source)->toBe(JobPost::SOURCE_DIRECT)
        ->and($job->jobSkills()->where('is_required', true)->value('skill_id'))->toBe($required->id)
        ->and($job->jobSkills()->where('is_required', false)->value('skill_id'))->toBe($preferred->id);
})->with(['admin', 'super_admin']);

it('rejects inactive or missing companies', function (bool $inactive, string $field) {
    $admin = User::factory()->create(['role' => 'admin']);
    $companyId = $inactive ? Company::factory()->create(['is_active' => false])->id : 999999;
    $payload = [
        'company_id' => $companyId, 'title' => 'Engineer', 'description' => 'Build services.',
        'application_method' => 'internal',
    ];

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/jobs', $payload)
        ->assertUnprocessable()->assertJsonValidationErrors([$field]);
})->with([
    'inactive' => [true, 'company_id'],
    'missing' => [false, 'company_id'],
]);

it('validates application method combinations', function (array $payload, string $field) {
    $admin = User::factory()->create(['role' => 'admin']);
    $payload = array_merge([
        'company_id' => Company::factory()->create()->id,
        'title' => 'Engineer', 'description' => 'Build services.',
    ], $payload);

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/jobs', $payload)
        ->assertUnprocessable()->assertJsonValidationErrors([$field]);
})->with([
    'external without URL' => [['application_method' => 'external'], 'application_url'],
    'external invalid URL' => [['application_method' => 'external', 'application_url' => 'invalid'], 'application_url'],
    'internal with URL' => [['application_method' => 'internal', 'application_url' => 'https://example.com'], 'application_url'],
]);

it('validates experience dates and skill group integrity', function (array $extra, string $field) {
    $admin = User::factory()->create(['role' => 'admin']);
    $skill = Skill::factory()->create();
    $payload = array_merge([
        'company_id' => Company::factory()->create()->id,
        'title' => 'Engineer', 'description' => 'Build services.',
        'application_method' => 'internal',
    ], $extra);

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/jobs', $payload)
        ->assertUnprocessable()->assertJsonValidationErrors([$field]);
})->with([
    'experience order' => [['min_years_experience' => 3, 'max_years_experience' => 1], 'max_years_experience'],
    'past expiration' => [['expires_at' => now()->subDay()->toDateTimeString()], 'expires_at'],
    'duplicate required' => [['required_skills' => [['skill_id' => $skill->id], ['skill_id' => $skill->id]]], 'required_skills'],
    'overlap' => [['required_skills' => [['skill_id' => $skill->id]], 'preferred_skills' => [['skill_id' => $skill->id]]], 'preferred_skills'],
]);

it('rolls back job creation when skill validation fails', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/jobs', [
        'company_id' => Company::factory()->create()->id,
        'title' => 'Engineer', 'description' => 'Build services.', 'application_method' => 'internal',
        'required_skills' => [['skill_id' => 999999]],
    ])->assertUnprocessable();

    $this->assertDatabaseCount('job_posts', 0);
});

it('updates fields and synchronizes only supplied skill categories', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $oldRequired = Skill::factory()->create();
    $newRequired = Skill::factory()->create();
    $preferred = Skill::factory()->create();
    $job = JobPost::factory()->for($company)->create([
        'created_by' => $admin->id, 'source' => JobPost::SOURCE_DIRECT,
        'application_method' => JobPost::APPLICATION_EXTERNAL, 'application_url' => 'https://old.example/apply',
    ]);
    JobSkill::factory()->for($job)->for($oldRequired)->create(['is_required' => true]);
    JobSkill::factory()->for($job)->for($preferred)->create(['is_required' => false]);

    $this->withToken(JWTAuth::fromUser($admin))->patchJson('/api/admin/jobs/'.$job->id, [
        'company_id' => $otherCompany->id,
        'title' => 'Updated Engineer',
        'required_skills' => [['skill_id' => $newRequired->id, 'importance' => 4]],
    ])->assertOk()->assertJsonPath('data.id', $job->id);

    $updated = $job->fresh();
    expect($updated->company_id)->toBe($otherCompany->id)
        ->and($updated->created_by)->toBe($admin->id)
        ->and($updated->source)->toBe(JobPost::SOURCE_DIRECT)
        ->and($updated->requiredSkills->modelKeys())->toBe([$newRequired->id])
        ->and($updated->preferredSkills->modelKeys())->toBe([$preferred->id])
        ->and($updated->jobSkills()->where('skill_id', $oldRequired->id)->exists())->toBeFalse();
});

it('clears internal application URL and requires a URL when switching to external', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $job = JobPost::factory()->create([
        'application_method' => JobPost::APPLICATION_EXTERNAL,
        'application_url' => 'https://example.com/apply',
    ]);
    $token = JWTAuth::fromUser($admin);

    $this->withToken($token)->patchJson('/api/admin/jobs/'.$job->id, [
        'application_method' => JobPost::APPLICATION_INTERNAL,
    ])->assertOk();
    expect($job->fresh()->application_url)->toBeNull();

    $this->withToken($token)->patchJson('/api/admin/jobs/'.$job->id, [
        'application_method' => JobPost::APPLICATION_EXTERNAL,
    ])->assertUnprocessable()->assertJsonValidationErrors(['application_url']);

    $this->withToken($token)->patchJson('/api/admin/jobs/'.$job->id, [
        'application_method' => JobPost::APPLICATION_EXTERNAL,
        'application_url' => 'https://example.com/new-apply',
    ])->assertOk();
    expect($job->fresh()->application_url)->toBe('https://example.com/new-apply');
});

it('returns complete job details with company, skill metadata and expiration state', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $company = Company::factory()->create(['name' => 'Example Company']);
    $required = Skill::factory()->create(['name' => 'Laravel']);
    $preferred = Skill::factory()->create(['name' => 'Docker']);
    $job = JobPost::factory()->for($company)->create([
        'published_at' => now()->subDays(3), 'expires_at' => now()->subDay(),
        'application_method' => JobPost::APPLICATION_EXTERNAL, 'application_url' => 'https://example.com/apply',
    ]);
    JobSkill::factory()->for($job)->for($required)->create(['is_required' => true, 'importance' => 5, 'required_level' => 'advanced']);
    JobSkill::factory()->for($job)->for($preferred)->create(['is_required' => false, 'importance' => 2, 'required_level' => 'intermediate']);

    $this->withToken(JWTAuth::fromUser($admin))->getJson('/api/admin/jobs/'.$job->id)
        ->assertOk()->assertJsonPath('data.company.id', $company->id)
        ->assertJsonPath('data.required_skills.0.name', 'Laravel')
        ->assertJsonPath('data.required_skills.0.required_level', 'advanced')
        ->assertJsonPath('data.preferred_skills.0.name', 'Docker')
        ->assertJsonPath('data.application_url', 'https://example.com/apply')
        ->assertJsonPath('data.is_expired', true)
        ->assertJsonPath('data.published_at', $job->published_at->toISOString());
});
