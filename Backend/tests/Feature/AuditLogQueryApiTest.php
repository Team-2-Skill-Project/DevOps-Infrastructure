<?php

use App\Models\AuditLog;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

function auditAdminToken(string $role = 'admin', bool $active = true): string
{
    return JWTAuth::fromUser(User::factory()->create(['role' => $role, 'is_active' => $active]));
}

function seedAuditMatrix(): array
{
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $other = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);

    $first = AuditLog::factory()->create([
        'actor_id' => $admin->id, 'action' => 'company_created', 'entity_type' => 'company',
        'entity_id' => 1, 'source' => 'admin', 'created_at' => '2026-09-05 10:00:00',
    ]);
    $second = AuditLog::factory()->create([
        'actor_id' => $other->id, 'action' => 'application_status_changed', 'entity_type' => 'application',
        'entity_id' => 9, 'source' => 'candidate',
        'before' => ['status' => 'applied'], 'after' => ['status' => 'interview'],
        'created_at' => '2026-09-10 10:00:00',
    ]);
    $third = AuditLog::factory()->create([
        'actor_id' => null, 'action' => 'ingestion_run_finished', 'entity_type' => 'ingestion_run',
        'entity_id' => 3, 'source' => 'ingestion', 'created_at' => '2026-09-15 10:00:00',
    ]);

    return [$admin, $other, $first, $second, $third];
}

it('requires JWT for the audit query', function () {
    $this->getJson('/api/admin/audit-logs')->assertUnauthorized();
});

it('rejects candidates and inactive admins', function (string $role, bool $active) {
    $this->withToken(auditAdminToken($role, $active))->getJson('/api/admin/audit-logs')->assertForbidden();
})->with([['candidate', true], ['admin', false], ['super_admin', false]]);

it('allows active admins and returns newest first with actor names', function () {
    [$admin, $other, $first, $second, $third] = seedAuditMatrix();

    $response = $this->withToken(auditAdminToken())->getJson('/api/admin/audit-logs')->assertOk();

    expect($response->json('meta.total'))->toBe(3)
        ->and($response->json('data.0.id'))->toBe($third->id)
        ->and($response->json('data.1.id'))->toBe($second->id)
        ->and($response->json('data.2.id'))->toBe($first->id)
        ->and($response->json('data.1.actor'))->toBe(['id' => $other->id, 'name' => $other->name])
        ->and($response->json('data.2.actor'))->toBe(['id' => $admin->id, 'name' => $admin->name])
        ->and($response->json('data.0.actor'))->toBeNull();
});

it('paginates the audit query', function () {
    seedAuditMatrix();

    $response = $this->withToken(auditAdminToken())->getJson('/api/admin/audit-logs?per_page=2')->assertOk();

    expect($response->json('meta.total'))->toBe(3)
        ->and($response->json('meta.per_page'))->toBe(2)
        ->and(count($response->json('data')))->toBe(2);
});

it('filters by actor, action, entity and source', function () {
    [$admin, $other, $first, $second, $third] = seedAuditMatrix();
    $token = auditAdminToken();

    expect($this->withToken($token)->getJson("/api/admin/audit-logs?actor_id={$other->id}")->json('data.0.id'))->toBe($second->id);
    expect($this->withToken($token)->getJson('/api/admin/audit-logs?action=company_created')->json('data.0.id'))->toBe($first->id);
    expect($this->withToken($token)->getJson('/api/admin/audit-logs?entity_type=application')->json('data.0.id'))->toBe($second->id);
    expect($this->withToken($token)->getJson('/api/admin/audit-logs?entity_type=application&entity_id=9')->json('meta.total'))->toBe(1);
    expect($this->withToken($token)->getJson('/api/admin/audit-logs?source=ingestion')->json('data.0.id'))->toBe($third->id);
    expect($this->withToken($token)->getJson("/api/admin/audit-logs?actor_id={$admin->id}&action=company_created&entity_type=company&entity_id=1&source=admin")->json('meta.total'))->toBe(1);
});

it('filters by date range', function () {
    seedAuditMatrix();
    $token = auditAdminToken();

    expect($this->withToken($token)->getJson('/api/admin/audit-logs?date_from=2026-09-08')->json('meta.total'))->toBe(2);
    expect($this->withToken($token)->getJson('/api/admin/audit-logs?date_to=2026-09-08')->json('meta.total'))->toBe(1);
    expect($this->withToken($token)->getJson('/api/admin/audit-logs?date_from=2026-09-08&date_to=2026-09-12')->json('meta.total'))->toBe(1);
});

it('searches scalar identifier fields only', function () {
    [$admin, $other, $first, $second, $third] = seedAuditMatrix();
    $token = auditAdminToken();

    expect($this->withToken($token)->getJson('/api/admin/audit-logs?search=ingestion')->json('data.0.id'))->toBe($third->id);
    expect($this->withToken($token)->getJson('/api/admin/audit-logs?search=APPLICATION')->json('data.0.id'))->toBe($second->id);
});

it('validates filter values', function () {
    $token = auditAdminToken();

    $this->withToken($token)->getJson('/api/admin/audit-logs?action=nope')->assertUnprocessable();
    $this->withToken($token)->getJson('/api/admin/audit-logs?entity_type=nope')->assertUnprocessable();
    $this->withToken($token)->getJson('/api/admin/audit-logs?source=nope')->assertUnprocessable();
    $this->withToken($token)->getJson('/api/admin/audit-logs?per_page=500')->assertUnprocessable();
    $this->withToken($token)->getJson('/api/admin/audit-logs?date_to=2026-09-01&date_from=2026-09-10')->assertUnprocessable();
});

it('localizes filter validation without translating stored values', function (string $language, string $attribute) {
    seedAuditMatrix();

    $response = $this->withToken(auditAdminToken())
        ->withHeader('Accept-Language', $language)
        ->getJson('/api/admin/audit-logs?action=nope')
        ->assertUnprocessable();

    expect($response->json('errors.action.0'))->toContain($attribute);

    $list = $this->withToken(auditAdminToken())
        ->withHeader('Accept-Language', $language)
        ->getJson('/api/admin/audit-logs?entity_type=application')
        ->assertOk();

    expect($list->json('data.0.action'))->toBe('application_status_changed')
        ->and($list->json('data.0.entity_type'))->toBe('application')
        ->and($list->json('data.0.source'))->toBe('candidate')
        ->and($list->json('data.0.before'))->toBe(['status' => 'applied']);
})->with([
    'English' => ['en', 'action'],
    'regional English' => ['en-US', 'action'],
    'Arabic' => ['ar', 'الإجراء'],
    'regional Arabic' => ['ar-EG', 'الإجراء'],
    'unsupported falls back to English' => ['fr-FR', 'action'],
]);
