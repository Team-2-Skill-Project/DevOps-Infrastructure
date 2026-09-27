<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\User;
use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationStatusHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'changed_by' => User::factory(),
            'old_status' => ApplicationStatus::APPLIED,
            'new_status' => ApplicationStatus::IN_REVIEW,
            'notes' => $this->faker->sentence(),
        ];
    }
}
