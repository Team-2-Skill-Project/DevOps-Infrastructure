<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MentorChat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MentorChat>
 */
class MentorChatFactory extends Factory
{
    /** @var class-string<MentorChat> @extends \Illuminate\Database\Eloquent\Factories\Factory<MentorChat> */
    protected $model = MentorChat::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'job_id' => null,
            'title' => $this->faker->sentence(4),
        ];
    }
}
