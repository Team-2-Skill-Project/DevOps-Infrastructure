<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\ApplicationStatusHistory as ApplicationStatusHistoryModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationStatusHistoryModel>
 */
class ApplicationStatusHistoryFactory extends Factory
{
    /** @var class-string<ApplicationStatusHistory> @extends \Illuminate\Database\Eloquent\Factories\Factory<ApplicationStatusHistory> */
    protected $model = ApplicationStatusHistoryModel::class;

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
