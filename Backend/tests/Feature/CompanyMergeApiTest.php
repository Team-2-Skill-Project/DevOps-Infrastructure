<?php

use App\Models\Company;
use App\Models\CompanyAlias;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

it('requires an active administrator for company merges', function (string $role, bool $active) {
    $user = User::factory()->create(['role' => $role, 'is_active' => $active]);

    $this->withToken(JWTAuth::fromUser($user))->postJson('/api/admin/companies/1/merge', [
        'target_company_id' => 2,
    ])->assertForbidden();
})->with([
    ['candidate', true],
    ['admin', false],
    ['super_admin', false],
]);

it('requires JWT for company merges', function () {
    $this->postJson('/api/admin/companies/1/merge', ['target_company_id' => 2])->assertUnauthorized();
});

it('validates the merge target', function (array $payload) {
    $admin = User::factory()->create(['role' => 'admin']);
    $source = Company::factory()->create();

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/companies/'.$source->id.'/merge', $payload)
        ->assertUnprocessable()->assertJsonValidationErrors(['target_company_id']);

    $this->assertModelExists($source);
})->with([
    'missing' => [[]],
    'missing target' => [['target_company_id' => 999999]],
    'invalid type' => [['target_company_id' => []]],
]);

it('rejects merging a company into itself', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $source = Company::factory()->create();

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/companies/'.$source->id.'/merge', [
        'target_company_id' => $source->id,
    ])->assertUnprocessable()->assertJsonValidationErrors(['target_company_id']);
});

it('merges companies while preserving target identity and moving jobs and aliases', function (string $role) {
    $admin = User::factory()->create(['role' => $role]);
    $source = Company::factory()->create([
        'name' => 'Google LLC', 'normalized_name' => 'google llc', 'website_url' => 'https://source.example.com',
        'description' => 'Source description', 'is_verified' => true,
    ]);
    $target = Company::factory()->create([
        'name' => 'Google', 'normalized_name' => 'google', 'website_url' => null,
        'description' => null, 'is_verified' => false, 'is_active' => false,
        'created_by' => $admin->id,
    ]);
    $sourceAlias = CompanyAlias::factory()->for($source)->create(['alias' => 'G Suite', 'normalized_alias' => 'g suite']);
    $targetAlias = CompanyAlias::factory()->for($target)->create(['alias' => 'Google Workspace', 'normalized_alias' => 'google workspace']);
    $job = JobPost::factory()->for($source)->create(['title' => 'Backend Engineer']);
    $jobAttributes = $job->refresh()->getAttributes();
    $targetId = $target->id;

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/companies/'.$source->id.'/merge', [
        'target_company_id' => $targetId,
    ])->assertOk()->assertJsonPath('data.id', $targetId)
        ->assertJsonPath('meta.merged_company_id', $source->id)
        ->assertJsonPath('meta.target_company_id', $targetId)
        ->assertJsonPath('meta.job_posts_moved', 1)
        ->assertJsonPath('meta.aliases_moved', 1)
        ->assertJsonPath('meta.source_name_aliases_created', 1);

    $this->assertModelMissing($source);
    $this->assertModelExists($target);
    $this->assertModelExists($targetAlias);
    expect($target->fresh()->only(['id', 'name', 'normalized_name', 'website_url', 'description', 'is_verified', 'is_active', 'created_by']))
        ->toBe([
            'id' => $targetId, 'name' => 'Google', 'normalized_name' => 'google',
            'website_url' => 'https://source.example.com', 'description' => 'Source description',
            'is_verified' => true, 'is_active' => false, 'created_by' => $admin->id,
        ]);
    expect($job->fresh()->getAttributes())->toMatchArray([
        'id' => $jobAttributes['id'], 'company_id' => $targetId, 'title' => 'Backend Engineer',
    ]);
    expect($sourceAlias->fresh()->company_id)->toBe($targetId);
    $this->assertDatabaseHas('company_aliases', ['company_id' => $targetId, 'normalized_alias' => 'google llc']);
})->with(['admin', 'super_admin']);

it('collapses aliases that already belong to the target or equal its canonical name', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $source = Company::factory()->create(['name' => 'Google LLC', 'normalized_name' => 'google llc']);
    $target = Company::factory()->create(['name' => 'Google', 'normalized_name' => 'google']);
    $targetAlias = CompanyAlias::factory()->for($target)->create(['alias' => 'G Cloud', 'normalized_alias' => 'g cloud']);
    $movedAlias = CompanyAlias::factory()->for($source)->create(['alias' => 'G Suite', 'normalized_alias' => 'g suite']);
    $canonicalDuplicate = CompanyAlias::factory()->for($source)->create(['alias' => 'Google', 'normalized_alias' => 'google']);

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/companies/'.$source->id.'/merge', [
        'target_company_id' => $target->id,
    ])->assertOk()->assertJsonPath('meta.aliases_collapsed', 1)->assertJsonPath('meta.source_name_aliases_created', 1);

    $this->assertModelExists($movedAlias);
    $this->assertModelMissing($canonicalDuplicate);
    $this->assertModelExists($targetAlias);
    $this->assertDatabaseCount('company_aliases', 3);
});

it('rejects third-party alias collisions and rolls back the merge', function (string $collisionType) {
    $admin = User::factory()->create(['role' => 'admin']);
    $source = Company::factory()->create(['name' => 'Google LLC', 'normalized_name' => 'google llc']);
    $target = Company::factory()->create(['name' => 'Google', 'normalized_name' => 'google']);
    $job = JobPost::factory()->for($source)->create();
    $sourceAlias = CompanyAlias::factory()->for($source)->create(['alias' => 'G Suite', 'normalized_alias' => 'g suite']);
    $thirdParty = Company::factory()->create();
    if ($collisionType === 'canonical') {
        $thirdParty->update(['name' => 'G Suite', 'normalized_name' => 'g suite']);
    } else {
        CompanyAlias::factory()->for($thirdParty)->create(['alias' => 'Google LLC', 'normalized_alias' => 'google llc']);
    }

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/companies/'.$source->id.'/merge', [
        'target_company_id' => $target->id,
    ])->assertUnprocessable()->assertJsonValidationErrors(['target_company_id']);

    expect($source->fresh())->not->toBeNull()
        ->and($target->fresh()->id)->toBe($target->id)
        ->and($job->fresh()->company_id)->toBe($source->id)
        ->and($sourceAlias->fresh()->company_id)->toBe($source->id);
})->with(['canonical', 'alias']);

it('rolls back all changes when source deletion fails late', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $source = Company::factory()->create(['name' => 'Google LLC', 'normalized_name' => 'google llc']);
    $target = Company::factory()->create(['name' => 'Google', 'normalized_name' => 'google']);
    $job = JobPost::factory()->for($source)->create();
    $alias = CompanyAlias::factory()->for($source)->create(['alias' => 'G Suite', 'normalized_alias' => 'g suite']);
    $sourceId = $source->id;
    $targetId = $target->id;

    Event::listen('eloquent.deleting: '.Company::class, function (Company $company) use ($sourceId): void {
        if ($company->id === $sourceId) {
            throw new RuntimeException('Intentional merge failure');
        }
    });

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/companies/'.$sourceId.'/merge', [
        'target_company_id' => $targetId,
    ])->assertInternalServerError();

    expect($source->fresh()->id)->toBe($sourceId)
        ->and($target->fresh()->id)->toBe($targetId)
        ->and($job->fresh()->company_id)->toBe($sourceId)
        ->and($alias->fresh()->company_id)->toBe($sourceId);
    $this->assertDatabaseMissing('company_aliases', ['company_id' => $targetId, 'normalized_alias' => 'google llc']);
});

it('makes the source name and aliases searchable on the target after merging', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $source = Company::factory()->create(['name' => 'Google LLC', 'normalized_name' => 'google llc']);
    $target = Company::factory()->create(['name' => 'Google', 'normalized_name' => 'google']);
    CompanyAlias::factory()->for($source)->create(['alias' => 'G Suite', 'normalized_alias' => 'g suite']);

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/companies/'.$source->id.'/merge', [
        'target_company_id' => $target->id,
    ])->assertOk();

    foreach (['Google LLC', 'G Suite'] as $search) {
        $this->withToken(JWTAuth::fromUser($admin))->getJson('/api/admin/companies?search='.urlencode($search))
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $target->id);
    }
});
