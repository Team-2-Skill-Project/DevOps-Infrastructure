<?php

use App\Enums\AuditAction;
use App\Enums\AuditEntityType;
use App\Enums\AuditSource;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditService;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

it('records an audit entry with actor, action, entity, source and snapshots', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $log = app(AuditService::class)->record(
        AuditAction::APPLICATION_STATUS_CHANGED,
        AuditEntityType::APPLICATION,
        123,
        AuditSource::ADMIN,
        $admin,
        ['status' => 'applied'],
        ['status' => 'in_review'],
        ['notes' => 'Moving forward'],
    );

    expect($log->action)->toBe(AuditAction::APPLICATION_STATUS_CHANGED)
        ->and($log->entity_type)->toBe(AuditEntityType::APPLICATION)
        ->and($log->entity_id)->toBe(123)
        ->and($log->source)->toBe(AuditSource::ADMIN)
        ->and($log->actor->is($admin))->toBeTrue()
        ->and($log->before)->toBe(['status' => 'applied'])
        ->and($log->after)->toBe(['status' => 'in_review'])
        ->and($log->metadata)->toBe(['notes' => 'Moving forward'])
        ->and($log->created_at)->not->toBeNull();
    $this->assertDatabaseHas('audit_logs', [
        'action' => 'application_status_changed',
        'entity_type' => 'application',
        'entity_id' => 123,
        'source' => 'admin',
        'actor_id' => $admin->id,
    ]);
});

it('supports system actors with null actor id', function () {
    $log = app(AuditService::class)->record(
        AuditAction::INGESTION_RUN_FINISHED,
        AuditEntityType::INGESTION_RUN,
        7,
        AuditSource::INGESTION,
    );

    expect($log->actor_id)->toBeNull()
        ->and($log->actor)->toBeNull()
        ->and($log->source)->toBe(AuditSource::INGESTION)
        ->and($log->before)->toBeNull()
        ->and($log->after)->toBeNull()
        ->and($log->metadata)->toBeNull();
});

it('strips sensitive keys from snapshots at any nesting level', function () {
    $log = app(AuditService::class)->record(
        AuditAction::COMPANY_UPDATED,
        AuditEntityType::COMPANY,
        1,
        AuditSource::ADMIN,
        null,
        ['name' => 'Acme', 'password' => 'secret', 'nested' => ['api_key' => 'x', 'city' => 'Cairo']],
        ['reset_token' => 'abc', 'otp' => '123456', 'is_active' => true],
        ['Authorization' => 'Bearer x', 'job_posts_moved' => 2],
    );

    expect($log->before)->toBe(['name' => 'Acme', 'nested' => ['city' => 'Cairo']])
        ->and($log->after)->toBe(['is_active' => true])
        ->and($log->metadata)->toBe(['job_posts_moved' => 2]);
    expect(json_encode($log->toArray()))->not->toContain('secret')
        ->and(json_encode($log->toArray()))->not->toContain('Bearer');
});

it('resolves no actor outside an authenticated request', function () {
    expect(app(AuditService::class)->currentActor())->toBeNull();
});

it('is append-only with no update timestamp', function () {
    $log = AuditLog::factory()->create();

    expect($log->getAttribute('updated_at'))->toBeNull()
        ->and($log->isClean())->toBeTrue();

    $response = $this->withToken(JWTAuth::fromUser(User::factory()->create(['role' => 'admin', 'is_active' => true])))
        ->patchJson("/api/admin/audit-logs/{$log->id}", ['action' => 'job_created']);
    $response->assertNotFound();

    $this->deleteJson("/api/admin/audit-logs/{$log->id}")->assertNotFound();
    expect(AuditLog::findOrFail($log->id)->action)->toBe($log->action);
});
