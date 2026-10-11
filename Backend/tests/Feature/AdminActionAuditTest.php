<?php

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\JobPost;
use App\Models\Skill;
use App\Models\SkillAlias;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

function adminToken(string $role = 'admin'): string
{
    return JWTAuth::fromUser(User::factory()->create(['role' => $role, 'is_active' => true]));
}

function latestAudit(string $action): AuditLog
{
    return AuditLog::query()->where('action', $action)->latest('id')->firstOrFail();
}

it('audits company creation with after snapshot and actor', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $id = $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/companies', [
        'name' => 'Acme Corp',
        'country' => 'Egypt',
        'is_verified' => true,
    ])->assertCreated()->json('data.id');

    $log = latestAudit('company_created');
    expect($log->entity_type->value)->toBe('company')
        ->and($log->entity_id)->toBe($id)
        ->and($log->source->value)->toBe('admin')
        ->and($log->actor->is($admin))->toBeTrue()
        ->and($log->before)->toBeNull()
        ->and($log->after['name'])->toBe('Acme Corp')
        ->and($log->after['country'])->toBe('Egypt')
        ->and($log->after['is_verified'])->toBeTrue();
});

it('audits company updates with before and after snapshots', function () {
    $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
    $company = Company::factory()->create(['name' => 'Old Name', 'city' => 'Cairo']);

    $this->withToken(JWTAuth::fromUser($admin))->patchJson("/api/admin/companies/{$company->id}", [
        'name' => 'New Name',
    ])->assertOk();

    $log = latestAudit('company_updated');
    expect($log->entity_id)->toBe($company->id)
        ->and($log->before['name'])->toBe('Old Name')
        ->and($log->after['name'])->toBe('New Name')
        ->and($log->after['city'])->toBe('Cairo')
        ->and($log->actor->is($admin))->toBeTrue();
});

it('audits company merges with counters metadata', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $source = Company::factory()->create(['name' => 'Source Co']);
    $target = Company::factory()->create(['name' => 'Target Co']);
    JobPost::factory()->create(['company_id' => $source->id]);

    $this->withToken(JWTAuth::fromUser($admin))->postJson("/api/admin/companies/{$source->id}/merge", [
        'target_company_id' => $target->id,
    ])->assertOk();

    $log = latestAudit('company_merged');
    expect($log->entity_type->value)->toBe('company')
        ->and($log->entity_id)->toBe($target->id)
        ->and($log->before['source_company']['name'])->toBe('Source Co')
        ->and($log->after['target_company']['name'])->toBe('Target Co')
        ->and($log->metadata['job_posts_moved'])->toBe(1)
        ->and($log->metadata['target_company_id'])->toBe($target->id)
        ->and($log->actor->is($admin))->toBeTrue();
});

it('audits job creation and updates with skill lists', function () {
    $token = adminToken();
    $company = Company::factory()->create();
    $skill = Skill::factory()->create();

    $id = $this->withToken($token)->postJson('/api/admin/jobs', [
        'company_id' => $company->id,
        'title' => 'Backend Developer',
        'description' => 'Build APIs.',
        'required_skills' => [['skill_id' => $skill->id, 'importance' => 5]],
    ])->assertCreated()->json('data.id');

    $created = latestAudit('job_created');
    expect($created->entity_id)->toBe($id)
        ->and($created->before)->toBeNull()
        ->and($created->after['title'])->toBe('Backend Developer')
        ->and($created->after['required_skill_ids'])->toBe([$skill->id])
        ->and($created->after['preferred_skill_ids'])->toBe([]);

    $this->withToken($token)->patchJson("/api/admin/jobs/{$id}", ['title' => 'Senior Backend Developer'])->assertOk();

    $updated = latestAudit('job_updated');
    expect($updated->entity_id)->toBe($id)
        ->and($updated->before['title'])->toBe('Backend Developer')
        ->and($updated->after['title'])->toBe('Senior Backend Developer');
});

it('audits skill lifecycle including aliases and merges', function () {
    $token = adminToken();

    $skillId = $this->withToken($token)->postJson('/api/admin/skills', ['name' => 'Laravel'])->assertCreated()->json('data.id');
    expect(latestAudit('skill_created')->entity_id)->toBe($skillId);

    $this->withToken($token)->patchJson("/api/admin/skills/{$skillId}", ['name' => 'Laravel Framework'])->assertOk();
    $updated = latestAudit('skill_updated');
    expect($updated->before['name'])->toBe('Laravel')->and($updated->after['name'])->toBe('Laravel Framework');

    $aliasId = $this->withToken($token)->postJson("/api/admin/skills/{$skillId}/aliases", ['alias' => 'Laravel PHP'])->assertCreated()->json('data.id');
    $aliasCreated = latestAudit('skill_alias_created');
    expect($aliasCreated->entity_id)->toBe($skillId)
        ->and($aliasCreated->after)->toBe(['alias' => 'Laravel PHP'])
        ->and($aliasCreated->metadata['alias_id'])->toBe($aliasId);

    $this->withToken($token)->patchJson("/api/admin/skills/{$skillId}/aliases/{$aliasId}", ['alias' => 'Laravel PHP Framework'])->assertOk();
    expect(latestAudit('skill_alias_updated')->before)->toBe(['alias' => 'Laravel PHP']);

    $this->withToken($token)->deleteJson("/api/admin/skills/{$skillId}/aliases/{$aliasId}")->assertNoContent();
    $deleted = latestAudit('skill_alias_deleted');
    expect($deleted->before)->toBe(['alias' => 'Laravel PHP Framework'])->and($deleted->after)->toBeNull();
    expect(SkillAlias::find($aliasId))->toBeNull();

    $otherId = $this->withToken($token)->postJson('/api/admin/skills', ['name' => 'Symfony'])->assertCreated()->json('data.id');
    $this->withToken($token)->postJson("/api/admin/skills/{$otherId}/merge", ['target_skill_id' => $skillId])->assertOk();
    $merged = latestAudit('skill_merged');
    expect($merged->entity_id)->toBe($skillId)
        ->and($merged->metadata['target_skill_id'])->toBe($skillId)
        ->and($merged->metadata['merged_skill_id'])->toBe($otherId);
    expect(Skill::find($otherId))->toBeNull();
});

it('audits job source creation and updates', function () {
    $token = adminToken();

    $id = $this->withToken($token)->postJson('/api/admin/job-sources', [
        'name' => 'Example API',
        'slug' => 'example-api',
        'source_type' => 'public',
        'collection_method' => 'api',
        'base_url' => 'https://example.com/api',
    ])->assertCreated()->json('data.id');

    $created = latestAudit('job_source_created');
    expect($created->entity_id)->toBe($id)->and($created->after['slug'])->toBe('example-api');

    $this->withToken($token)->patchJson("/api/admin/job-sources/{$id}", ['is_active' => false])->assertOk();
    $updated = latestAudit('job_source_updated');
    expect($updated->before['is_active'])->toBeTrue()->and($updated->after['is_active'])->toBeFalse();
});

it('does not leave a success audit when the business mutation fails', function () {
    $token = adminToken();
    Company::factory()->create(['name' => 'Taken Name', 'normalized_name' => 'taken name']);

    $this->withToken($token)->postJson('/api/admin/companies', ['name' => 'Taken Name'])->assertUnprocessable();

    expect(AuditLog::query()->where('action', 'company_created')->count())->toBe(0);
    expect(Company::query()->where('name', 'Taken Name')->count())->toBe(1);
});
