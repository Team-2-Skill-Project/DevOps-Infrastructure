<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Application as ApplicationModel;
use App\Models\CandidateProfile;
use App\Models\JobPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationModel>
 */
class ApplicationFactory extends Factory
{
    /** @var class-string<Application> @extends \Illuminate\Database\Eloquent\Factories\Factory<Application> */
    protected $model = ApplicationModel::class;

    public function definition(): array
    {
        return [
            'candidate_profile_id' => CandidateProfile::factory(),
            'job_id' => JobPost::factory(),
            'status' => ApplicationStatus::APPLIED,
            'cover_letter' => $this->faker->paragraph(),
        ];
    }
}
