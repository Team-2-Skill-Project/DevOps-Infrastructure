<?php

namespace Database\Seeders;

use App\Models\CandidateProfile;
use App\Models\CvDocument;
use App\Models\CvExtraction;
use Illuminate\Database\Seeder;

class CvDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $candidates = CandidateProfile::all();

        if ($candidates->isEmpty()) {
            $candidates = CandidateProfile::factory(3)->create();
        }

        foreach ($candidates as $candidate) {
            $cv = CvDocument::factory()->create([
                'candidate_profile_id' => $candidate->id,
            ]);

            CvExtraction::factory()->create([
                'cv_document_id' => $cv->getKey(),
            ]);
        }
    }
}
