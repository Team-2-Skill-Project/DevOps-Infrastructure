<?php

use App\Models\Company;
use App\Models\CompanyAlias;
use App\Models\JobPost;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

dataset('company admin endpoints', [
    ['postJson', '/api/admin/companies'],
    ['patchJson', '/api/admin/companies/1'],
    ['getJson', '/api/admin/companies'],
    ['getJson', '/api/admin/companies/1'],
]);

it('requires JWT for company admin endpoints', function (string $method, string $url) {
    $this->{$method}($url)->assertUnauthorized();
})->with('company admin endpoints');

it('rejects candidates and inactive administrators', function (string $method, string $url, string $role, bool $active) {
    $user = User::factory()->create(['role' => $role, 'is_active' => $active]);

    $this->withToken(JWTAuth::fromUser($user))->{$method}($url)->assertForbidden();
})->with('company admin endpoints')->with([
    ['candidate', true],
    ['admin', false],
    ['super_admin', false],
]);

it('allows active administrators to create companies and ignores protected fields', function (string $role) {
    $admin = User::factory()->create(['role' => $role, 'is_active' => true]);

    $response = $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/companies', [
        'name' => '  Example Company  ',
        'website_url' => 'https://example.com',
        'industry' => 'Technology',
        'country' => 'Egypt',
        'is_verified' => true,
        'normalized_name' => 'spoofed',
        'created_by' => 999999,
        'id' => 999999,
        'created_at' => '2000-01-01',
    ])->assertCreated()
        ->assertJsonPath('data.name', 'Example Company')
        ->assertJsonPath('data.is_verified', true)
        ->assertJsonPath('data.is_active', true)
        ->assertJsonMissingPath('data.normalized_name');

    $company = Company::findOrFail($response->json('data.id'));

    expect($company->normalized_name)->toBe('example company')
        ->and($company->created_by)->toBe($admin->id)
        ->and($company->is_active)->toBeTrue();
})->with(['admin', 'super_admin']);

it('rejects duplicate company and alias names on creation', function (string $existingType) {
    $admin = User::factory()->create(['role' => 'admin']);
    $company = Company::factory()->create(['name' => 'International Business Machines', 'normalized_name' => 'international business machines']);
    if ($existingType === 'alias') {
        CompanyAlias::factory()->for($company)->create(['alias' => 'IBM', 'normalized_alias' => 'ibm']);
    }

    $name = $existingType === 'alias' ? ' ibm ' : ' INTERNATIONAL BUSINESS MACHINES ';
    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/companies', ['name' => $name])
        ->assertUnprocessable()->assertJsonValidationErrors(['name']);
})->with(['canonical', 'alias']);

it('updates company fields and preserves its relationships', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $company = Company::factory()->create(['name' => 'Old Company', 'normalized_name' => 'old company']);
    $alias = CompanyAlias::factory()->for($company)->create();
    $job = JobPost::factory()->for($company)->create();

    $this->withToken(JWTAuth::fromUser($admin))->patchJson('/api/admin/companies/'.$company->id, [
        'name' => '  New Company ',
        'description' => 'Updated description',
        'is_verified' => true,
    ])->assertOk()->assertJsonPath('data.id', $company->id)
        ->assertJsonPath('data.name', 'New Company')
        ->assertJsonPath('data.is_verified', true);

    $updated = $company->fresh();
    expect($updated->normalized_name)->toBe('new company')
        ->and($updated->description)->toBe('Updated description')
        ->and($updated->aliases()->whereKey($alias->id)->exists())->toBeTrue()
        ->and($updated->jobPosts()->whereKey($job->id)->exists())->toBeTrue();
});

it('rejects duplicate and alias collisions when renaming', function (string $collision) {
    $admin = User::factory()->create(['role' => 'admin']);
    $other = Company::factory()->create(['name' => 'Existing Company', 'normalized_name' => 'existing company']);
    $company = Company::factory()->create(['name' => 'Editable Company', 'normalized_name' => 'editable company']);
    if ($collision === 'alias') {
        CompanyAlias::factory()->for($other)->create(['alias' => 'Known Alias', 'normalized_alias' => 'known alias']);
        $name = 'known alias';
    } else {
        $name = ' existing company ';
    }

    $this->withToken(JWTAuth::fromUser($admin))->patchJson('/api/admin/companies/'.$company->id, ['name' => $name])
        ->assertUnprocessable()->assertJsonValidationErrors(['name']);

    expect($company->fresh()->normalized_name)->toBe('editable company');
})->with(['canonical', 'alias']);

it('returns company details with aliases and job count', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $company = Company::factory()->create();
    $alias = CompanyAlias::factory()->for($company)->create();
    JobPost::factory()->count(2)->for($company)->create();

    $this->withToken(JWTAuth::fromUser($admin))->getJson('/api/admin/companies/'.$company->id)
        ->assertOk()->assertJsonPath('data.id', $company->id)
        ->assertJsonPath('data.aliases.0.id', $alias->id)
        ->assertJsonPath('data.job_posts_count', 2)
        ->assertJsonMissingPath('data.normalized_name');
});

it('lists canonical companies with search filters and pagination', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $matched = Company::factory()->create([
        'name' => 'Alpha Company', 'normalized_name' => 'alpha company', 'industry' => 'Technology',
        'country' => 'Egypt', 'is_verified' => true, 'is_active' => false,
    ]);
    CompanyAlias::factory()->for($matched)->create(['alias' => 'Alpha Brand', 'normalized_alias' => 'alpha brand']);
    Company::factory()->create(['name' => 'Beta Company', 'normalized_name' => 'beta company', 'industry' => 'Finance', 'country' => 'Jordan']);
    Company::factory()->create(['name' => 'Gamma Company', 'normalized_name' => 'gamma company', 'industry' => 'Technology', 'country' => 'Egypt']);

    $token = JWTAuth::fromUser($admin);
    $this->withToken($token)->getJson('/api/admin/companies?search=alpha&is_verified=true&is_active=false&industry=Technology&country=Egypt&per_page=1')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $matched->id)
        ->assertJsonPath('meta.per_page', 1)->assertJsonPath('meta.total', 1);

    $this->withToken($token)->getJson('/api/admin/companies?search=brand')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $matched->id);
});

it('orders company pages newest first and validates page size', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $first = Company::factory()->create();
    $second = Company::factory()->create();

    $this->withToken(JWTAuth::fromUser($admin))->getJson('/api/admin/companies?per_page=101')
        ->assertUnprocessable()->assertJsonValidationErrors(['per_page']);

    $this->withToken(JWTAuth::fromUser($admin))->getJson('/api/admin/companies?per_page=1')
        ->assertOk()->assertJsonPath('data.0.id', $second->id)
        ->assertJsonPath('meta.last_page', 2);

    expect($first->id)->toBeLessThan($second->id);
});

it('toggles verification and deactivates a company without removing jobs or aliases', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $company = Company::factory()->create(['is_verified' => false, 'is_active' => true]);
    $alias = CompanyAlias::factory()->for($company)->create();
    $job = JobPost::factory()->for($company)->create();

    $this->withToken(JWTAuth::fromUser($admin))->patchJson('/api/admin/companies/'.$company->id, [
        'is_verified' => true, 'is_active' => false,
    ])->assertOk()->assertJsonPath('data.is_verified', true)->assertJsonPath('data.is_active', false);

    expect($company->fresh()->is_active)->toBeFalse()
        ->and(CompanyAlias::find($alias->id))->not->toBeNull()
        ->and(JobPost::find($job->id))->not->toBeNull();
});

it('validates required company input', function (array $payload, string $field) {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/companies', $payload)
        ->assertUnprocessable()->assertJsonValidationErrors([$field]);
})->with([
    [[], 'name'],
    [['name' => ' '], 'name'],
    [['name' => str_repeat('a', 256)], 'name'],
    [['name' => 'Example', 'website_url' => 'invalid'], 'website_url'],
]);
