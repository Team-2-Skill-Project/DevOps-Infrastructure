<?php

namespace Database\Factories;

use App\Enums\AuditAction;
use App\Enums\AuditEntityType;
use App\Enums\AuditSource;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_id' => User::factory(),
            'action' => AuditAction::APPLICATION_STATUS_CHANGED,
            'entity_type' => AuditEntityType::APPLICATION,
            'entity_id' => 1,
            'source' => AuditSource::ADMIN,
            'before' => ['status' => 'applied'],
            'after' => ['status' => 'in_review'],
            'metadata' => null,
        ];
    }
}
