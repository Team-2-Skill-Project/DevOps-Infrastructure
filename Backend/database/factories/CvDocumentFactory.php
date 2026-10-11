<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CvParsingStatus;
use App\Models\CandidateProfile;
use App\Models\CvDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CvDocument> */
class CvDocumentFactory extends Factory
{
    /** @var class-string<CvDocument> */
    protected $model = CvDocument::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'candidate_profile_id' => CandidateProfile::factory(),
            'original_filename' => $this->faker->word().'_CV.pdf',
            'storage_disk' => 'public',
            'storage_path' => 'cvs/'.$this->faker->uuid().'.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => $this->faker->numberBetween(100000, 2000000),
            'file_hash' => hash('sha256', uniqid()),
            'version' => 1,
            'is_current' => true,
            'status' => CvParsingStatus::COMPLETED,
            'processed_at' => now(),
            'failure_reason' => null,
        ];
    }
}
