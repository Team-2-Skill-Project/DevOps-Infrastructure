<?php

use App\Models\Company;
use App\Models\CompanyAlias;
use App\Models\JobPost;
use App\Models\JobSkill;
use App\Models\Skill;
use App\Models\SkillAlias;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

it('requires JWT and an active supported user', function (string $role, bool $active) {
    $user = User::factory()->create(['role' => $role, 'is_active' => $active]);

    $response = $this->withToken(JWTAuth::fromUser($user))->getJson('/api/jobs');

    $response->assertForbidden();
})->with([
    ['candidate', false], ['admin', false], ['super_admin', false], ['recruiter', true],
]);

it('requires JWT for the job feed', function () {
    $this->getJson('/api/jobs')->assertUnauthorized();
});

it('allows active candidates and administrators to read the feed', function (string $role) {
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);
    JobPost::factory()->create(['published_at' => now()]);

    $this->withToken(JWTAuth::fromUser($user))->getJson('/api/jobs')
        ->assertOk()->assertJsonCount(1, 'data');
})->with(['candidate', 'admin', 'super_admin']);

it('only includes active non-expired jobs from active companies', function () {
    $user = User::factory()->create(['role' => 'candidate']);
    $activeCompany = Company::factory()->create(['is_active' => true]);
    $inactiveCompany = Company::factory()->create(['is_active' => false]);
    $visible = JobPost::factory()->for($activeCompany)->create(['is_active' => true, 'expires_at' => null]);
    JobPost::factory()->for($activeCompany)->create(['is_active' => false]);
    JobPost::factory()->for($activeCompany)->create(['is_active' => true, 'expires_at' => now()->subMinute()]);
    JobPost::factory()->for($inactiveCompany)->create(['is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))->getJson('/api/jobs')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $visible->id)
        ->assertJsonPath('data.0.is_expired', false);
});

it('does not hide unverified companies and returns the normalized feed payload', function () {
    $user = User::factory()->create(['role' => 'candidate']);
    $company = Company::factory()->create(['is_verified' => false, 'name' => 'Example Company']);
    $required = Skill::factory()->create(['name' => 'Laravel']);
    $preferred = Skill::factory()->create(['name' => 'Docker']);
    $job = JobPost::factory()->for($company)->create(['published_at' => now()]);
    JobSkill::factory()->for($job)->for($required)->create(['is_required' => true, 'importance' => 5, 'required_level' => 'advanced']);
    JobSkill::factory()->for($job)->for($preferred)->create(['is_required' => false, 'importance' => 2, 'required_level' => 'intermediate']);

    $this->withToken(JWTAuth::fromUser($user))->getJson('/api/jobs')
        ->assertOk()->assertJsonPath('data.0.company.id', $company->id)
        ->assertJsonPath('data.0.company.is_verified', false)
        ->assertJsonPath('data.0.required_skills.0.name', 'Laravel')
        ->assertJsonPath('data.0.required_skills.0.required_level', 'advanced')
        ->assertJsonPath('data.0.preferred_skills.0.name', 'Docker')
        ->assertJsonPath('data.0.source', $job->source)
        ->assertJsonPath('data.0.application_method', $job->application_method)
        ->assertJsonMissingPath('data.0.external_url');
});

it('paginates with fifteen by default and supports a maximum page size', function () {
    $user = User::factory()->create(['role' => 'candidate']);
    JobPost::factory()->count(16)->create(['published_at' => now()]);
    $token = JWTAuth::fromUser($user);

    $this->withToken($token)->getJson('/api/jobs')->assertOk()
        ->assertJsonCount(15, 'data')->assertJsonPath('meta.per_page', 15)->assertJsonPath('meta.total', 16);
    $this->withToken($token)->getJson('/api/jobs?per_page=100')->assertOk()->assertJsonCount(16, 'data');
    $this->withToken($token)->getJson('/api/jobs?per_page=101')->assertUnprocessable()->assertJsonValidationErrors(['per_page']);
});

it('searches title canonical role company name and aliases without duplicate jobs', function (string $search, string $expected) {
    $user = User::factory()->create(['role' => 'candidate']);
    $company = Company::factory()->create(['name' => 'Acme Holdings']);
    CompanyAlias::factory()->for($company)->create(['alias' => 'Acme Group', 'normalized_alias' => 'acme group']);
    $job = JobPost::factory()->for($company)->create([
        'title' => 'Backend Engineer', 'canonical_role' => 'Platform Engineer', 'description' => 'Build APIs.',
    ]);
    JobPost::factory()->create(['title' => 'Unrelated Position']);

    $this->withToken(JWTAuth::fromUser($user))->getJson('/api/jobs?search='.urlencode($search))
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $job->id)
        ->assertJsonPath('data.0.title', $expected);
})->with([
    'title' => ['backend', 'Backend Engineer'],
    'role' => ['platform', 'Backend Engineer'],
    'company' => ['holdings', 'Backend Engineer'],
    'alias' => ['group', 'Backend Engineer'],
]);

it('searches required and preferred skill names and skill aliases without duplicates', function (bool $required) {
    $user = User::factory()->create(['role' => 'candidate']);
    $skill = Skill::factory()->create(['name' => 'Laravel']);
    SkillAlias::factory()->for($skill)->create(['alias' => 'PHP Framework', 'normalized_alias' => 'php framework']);
    $job = JobPost::factory()->create();
    JobSkill::factory()->for($job)->for($skill)->create(['is_required' => $required]);
    JobPost::factory()->create();

    foreach (['Laravel', 'PHP Framework'] as $search) {
        $this->withToken(JWTAuth::fromUser($user))->getJson('/api/jobs?search='.urlencode($search))
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $job->id);
    }
})->with([true, false]);

it('ranks relevance matches before weaker matches and uses deterministic tie breaks', function () {
    $user = User::factory()->create(['role' => 'candidate']);
    $company = Company::factory()->create(['name' => 'Laravel Company']);
    $exact = JobPost::factory()->create(['title' => 'Laravel', 'description' => 'Unrelated text.']);
    $prefix = JobPost::factory()->create(['title' => 'Laravel Engineer', 'description' => 'Unrelated text.']);
    $companyMatch = JobPost::factory()->for($company)->create(['title' => 'Engineer', 'description' => 'Unrelated text.']);
    $descriptionMatch = JobPost::factory()->create(['title' => 'Engineer', 'description' => 'Uses Laravel daily.']);

    $response = $this->withToken(JWTAuth::fromUser($user))->getJson('/api/jobs?search=laravel&sort=relevance&per_page=100')->assertOk();

    expect(array_column($response->json('data'), 'id'))->toBe([$exact->id, $prefix->id, $companyMatch->id, $descriptionMatch->id]);
});

it('falls back to newest when relevance is requested without search and validates sort', function () {
    $user = User::factory()->create(['role' => 'candidate']);
    $new = JobPost::factory()->create(['published_at' => now()]);
    JobPost::factory()->create(['published_at' => now()->subDay()]);
    $token = JWTAuth::fromUser($user);

    $this->withToken($token)->getJson('/api/jobs?sort=relevance')
        ->assertOk()->assertJsonPath('data.0.id', $new->id);
    $this->withToken($token)->getJson('/api/jobs?sort=popular')
        ->assertUnprocessable()->assertJsonValidationErrors(['sort']);
});

it('returns an empty paginated result when no jobs match', function () {
    $user = User::factory()->create(['role' => 'candidate']);

    $this->withToken(JWTAuth::fromUser($user))->getJson('/api/jobs?search=does-not-exist')
        ->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('meta.total', 0)
        ->assertJsonPath('meta.current_page', 1)->assertJsonPath('meta.last_page', 1);
});

it('combines search, location, work mode, experience, skill filters, sorting, and pagination', function () {
    $user = User::factory()->create(['role' => 'candidate']);
    $required = Skill::factory()->create(['name' => 'Laravel']);
    $preferred = Skill::factory()->create(['name' => 'Docker']);
    $matching = JobPost::factory()->create([
        'title' => 'Laravel Engineer', 'country' => 'Egypt', 'work_mode' => 'remote',
        'experience_level' => 'junior', 'job_type' => 'job', 'published_at' => now(),
    ]);
    JobSkill::factory()->for($matching)->for($required)->create(['is_required' => true]);
    JobSkill::factory()->for($matching)->for($preferred)->create(['is_required' => false]);
    JobPost::factory()->create(['title' => 'Laravel Engineer', 'country' => 'Jordan', 'work_mode' => 'remote']);

    $this->withToken(JWTAuth::fromUser($user))->getJson(
        '/api/jobs?search=laravel&country=Egypt&work_mode=remote&experience_level=junior&job_type=job&required_skill_ids[]='.$required->id.'&preferred_skill_ids[]='.$preferred->id.'&sort=relevance&per_page=20'
    )->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $matching->id)
        ->assertJsonPath('meta.per_page', 20);
});

it('applies scalar filters', function (string $filter, string $value) {
    $user = User::factory()->create(['role' => 'candidate']);
    $company = Company::factory()->create();
    $matching = JobPost::factory()->for($company)->create([
        'job_type' => 'internship', 'work_mode' => 'remote', 'employment_type' => 'part_time',
        'experience_level' => 'entry', 'country' => 'Egypt', 'state' => 'Cairo', 'city' => 'Cairo',
        'source' => JobPost::SOURCE_DIRECT, 'application_method' => JobPost::APPLICATION_INTERNAL,
    ]);
    JobPost::factory()->for($company)->create(['job_type' => 'job', 'country' => 'Jordan', 'experience_level' => 'senior']);

    $this->withToken(JWTAuth::fromUser($user))->getJson('/api/jobs?'.$filter.'='.urlencode($value))
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $matching->id);
})->with([
    ['job_type', 'internship'], ['work_mode', 'remote'], ['employment_type', 'part_time'],
    ['experience_level', 'entry'], ['country', 'Egypt'], ['state', 'Cairo'], ['city', 'Cairo'],
    ['source', 'direct'], ['application_method', 'internal'],
]);

it('filters by company id', function () {
    $user = User::factory()->create(['role' => 'candidate']);
    $company = Company::factory()->create();
    $matching = JobPost::factory()->for($company)->create();
    JobPost::factory()->create();

    $this->withToken(JWTAuth::fromUser($user))->getJson('/api/jobs?company_id='.$company->id)
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $matching->id);
});

it('filters by company verification and validates filter values', function () {
    $user = User::factory()->create(['role' => 'candidate']);
    $verified = Company::factory()->create(['is_verified' => true]);
    $unverified = Company::factory()->create(['is_verified' => false]);
    $visible = JobPost::factory()->for($verified)->create();
    JobPost::factory()->for($unverified)->create();
    $token = JWTAuth::fromUser($user);

    $this->withToken($token)->getJson('/api/jobs?is_verified_company=true')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $visible->id);
    $this->withToken($token)->getJson('/api/jobs?job_type=invalid')
        ->assertUnprocessable()->assertJsonValidationErrors(['job_type']);
    $this->withToken($token)->getJson('/api/jobs?application_method=invalid')
        ->assertUnprocessable()->assertJsonValidationErrors(['application_method']);
});

it('uses AND semantics for required and preferred skill filters', function () {
    $user = User::factory()->create(['role' => 'candidate']);
    $first = Skill::factory()->create();
    $second = Skill::factory()->create();
    $third = Skill::factory()->create();
    $all = JobPost::factory()->create();
    $requiredOnly = JobPost::factory()->create();
    $preferredAll = JobPost::factory()->create();
    JobSkill::factory()->for($all)->for($first)->create(['is_required' => true]);
    JobSkill::factory()->for($all)->for($second)->create(['is_required' => true]);
    JobSkill::factory()->for($all)->for($third)->create(['is_required' => false]);
    JobSkill::factory()->for($requiredOnly)->for($first)->create(['is_required' => true]);
    JobSkill::factory()->for($preferredAll)->for($first)->create(['is_required' => false]);
    JobSkill::factory()->for($preferredAll)->for($second)->create(['is_required' => false]);
    $token = JWTAuth::fromUser($user);

    $this->withToken($token)->getJson('/api/jobs?required_skill_ids[]='.$first->id.'&required_skill_ids[]='.$second->id)
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $all->id);
    $this->withToken($token)->getJson('/api/jobs?preferred_skill_ids[]='.$first->id.'&preferred_skill_ids[]='.$second->id)
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $preferredAll->id);
    $this->withToken($token)->getJson('/api/jobs?required_skill_ids[]='.$third->id)
        ->assertOk()->assertJsonCount(0, 'data');
});

it('validates nonexistent and duplicate skill filters', function (string $query, string $field) {
    $user = User::factory()->create(['role' => 'candidate']);

    $this->withToken(JWTAuth::fromUser($user))->getJson('/api/jobs?'.$query)
        ->assertUnprocessable()->assertJsonValidationErrors([$field]);
})->with([
    ['required_skill_ids[]=999999', 'required_skill_ids.0'],
    ['preferred_skill_ids[]=999999', 'preferred_skill_ids.0'],
]);

it('orders jobs by published date then id with null dates last', function () {
    $user = User::factory()->create(['role' => 'candidate']);
    $old = JobPost::factory()->create(['published_at' => now()->subDay()]);
    $new = JobPost::factory()->create(['published_at' => now()]);
    $tieFirst = JobPost::factory()->create(['published_at' => now()->subHours(2)]);
    $tieSecond = JobPost::factory()->create(['published_at' => $tieFirst->published_at]);
    $null = JobPost::factory()->create(['published_at' => null]);

    $response = $this->withToken(JWTAuth::fromUser($user))->getJson('/api/jobs?per_page=100')->assertOk();

    expect(array_column($response->json('data'), 'id'))->toBe([$new->id, $tieSecond->id, $tieFirst->id, $old->id, $null->id]);
});
