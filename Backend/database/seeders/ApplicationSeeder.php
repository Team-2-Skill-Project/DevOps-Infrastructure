<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\CandidateProfile;
use App\Models\JobPost;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $candidates = CandidateProfile::all();
        $jobs = JobPost::all();

        if ($candidates->isEmpty() || $jobs->isEmpty()) {
            return;
        }

        foreach ($candidates as $candidate) {
            $randomJobs = $jobs->random(min(2, $jobs->count()));

            foreach ($randomJobs as $job) {
                $application = Application::firstOrCreate(
                    [
                        'candidate_profile_id' => $candidate->id,
                        'job_id' => $job->id,
                    ],
                    [
                        'status' => ApplicationStatus::APPLIED,
                        'cover_letter' => 'Hello, I am very interested in this position and confident my skills match your requirements.',
                    ]
                );

                ApplicationStatusHistory::firstOrCreate(
                    ['application_id' => $application->id],
                    [
                        'changed_by' => $candidate->user_id ?? null,
                        'old_status' => null,
                        'new_status' => ApplicationStatus::APPLIED,
                        'notes' => 'Initial application submission.',
                    ]
                );
            }
        }
    }
}
