<?php

namespace Database\Factories;

use App\Models\CandidateProfile;
use App\Models\JobMatch;
use App\Models\JobPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JobMatch> */
class JobMatchFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'candidate_profile_id' => CandidateProfile::factory(),
            'job_post_id' => JobPost::factory(),
            'score' => fake()->randomFloat(2, 0, 100),
        ];
    }
}
