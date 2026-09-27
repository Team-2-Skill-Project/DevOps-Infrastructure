<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => Notification::TYPE_RELEVANT_JOB,
            'title' => fake()->sentence(4),
            'message' => fake()->sentence(),
            'data' => null,
            'read_at' => null,
        ];
    }
}
