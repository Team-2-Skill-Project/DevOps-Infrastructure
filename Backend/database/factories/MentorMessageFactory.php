<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MentorMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MentorMessage>
 */
class MentorMessageFactory extends Factory
{
    /** @var class-string<MentorMessage> @extends \Illuminate\Database\Eloquent\Factories\Factory<MentorMessage> */
    protected $model = MentorMessage::class;

    public function definition(): array
    {
        return [
            'mentor_chat_id' => MentorChatFactory::new(),
            'sender' => 'mentor',
            'content' => $this->faker->paragraph(),
            'supported_actions' => json_encode([]),
        ];
    }
}
