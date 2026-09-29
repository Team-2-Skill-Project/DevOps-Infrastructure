<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CvParsingStatus;
use App\Models\CandidateProfile;
use App\Models\CvDocument;
use App\Models\CvDocument as CvDocumentModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CvDocumentModel>
 */
class CvDocumentFactory extends Factory
{
    /** @var class-string<CvDocument> @extends \Illuminate\Database\Eloquent\Factories\Factory<CvDocument> */
    protected $model = CvDocumentModel::class;

    public function definition(): array
    {
        return [
            'candidate_profile_id' => CandidateProfile::factory(),
            'original_filename' => sprintf('%s_CV.pdf', $this->faker->word()),
            'storage_disk' => 'public',
            'storage_path' => sprintf('cvs/%s.pdf', $this->faker->uuid()),
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
