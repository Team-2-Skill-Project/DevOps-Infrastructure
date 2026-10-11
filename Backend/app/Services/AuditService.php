<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\AuditEntityType;
use App\Enums\AuditSource;
use App\Models\AuditLog;
use App\Models\User;

class AuditService
{
    /**
     * Keys that must never be persisted inside audit snapshots, matched
     * case-insensitively against the key name at any nesting level.
     *
     * @var list<string>
     */
    private const SENSITIVE_KEY_FRAGMENTS = [
        'password',
        'passwd',
        'secret',
        'token',
        'jwt',
        'otp',
        'api_key',
        'apikey',
        'private_key',
        'credential',
        'authorization',
    ];

    /**
     * Record an append-only audit entry.
     *
     * Callers must pass explicit allowlisted snapshot data; arbitrary
     * Model::toArray() payloads are not accepted by convention and are
     * additionally stripped of sensitive keys as defense in depth.
     *
     * The call is intentionally not wrapped in its own transaction: it must
     * run inside the caller's business transaction so both commit together.
     *
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     * @param  array<string, mixed>|null  $metadata
     */
    public function record(
        AuditAction $action,
        AuditEntityType $entity,
        ?int $entityId,
        AuditSource $source,
        ?User $actor = null,
        ?array $before = null,
        ?array $after = null,
        ?array $metadata = null,
    ): AuditLog {
        return AuditLog::query()->create([
            'actor_id' => $actor?->getKey(),
            'action' => $action,
            'entity_type' => $entity,
            'entity_id' => $entityId,
            'source' => $source,
            'before' => $before === null ? null : $this->sanitize($before),
            'after' => $after === null ? null : $this->sanitize($after),
            'metadata' => $metadata === null ? null : $this->sanitize($metadata),
        ]);
    }

    /**
     * Resolve the currently authenticated API user, if any.
     */
    public function currentActor(): ?User
    {
        $user = auth('api')->user();

        return $user instanceof User ? $user : null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function sanitize(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            $name = strtolower((string) $key);
            $blocked = false;
            foreach (self::SENSITIVE_KEY_FRAGMENTS as $fragment) {
                if (str_contains($name, $fragment)) {
                    $blocked = true;
                    break;
                }
            }
            if ($blocked) {
                continue;
            }
            $clean[$key] = is_array($value) ? $this->sanitize($value) : $value;
        }

        return $clean;
    }
}
