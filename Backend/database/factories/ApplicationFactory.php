<?php

namespace Database\Factories;

use App\Models\CandidateProfile;
use App\Enums\ApplicationStatus;
use App\Models\JobPost;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFactory extends Factory
{
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
