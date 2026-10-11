<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\CandidateProfile;
use App\Models\JobPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Application> */
class ApplicationFactory extends Factory
{
    /** @var class-string<Application> */
    protected $model = Application::class;

    /** @return array<string, mixed> */
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
