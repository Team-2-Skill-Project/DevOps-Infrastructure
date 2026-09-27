<?php

namespace Database\Factories;

use App\Models\CvDocument;
use App\Enums\CvExtractionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class CvExtractionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cv_document_id' => CvDocument::factory(),
            'attempt_number' => 1,
            'status' => CvExtractionStatus::SUCCESS,
            'provider' => 'openai',
            'model' => 'gpt-4o',
            'parser_version' => '1.0.0',
            'raw_text' => 'Sample extracted text from CV containing skills, experience, and education.',
            'extracted_data' => [
                'skills' => ['PHP', 'Laravel', 'MySQL', 'Docker'],
                'experience_years' => 2,
            ],
            'confidence_score' => 0.9500,
            'error_message' => null,
            'started_at' => now()->subSeconds(5),
            'completed_at' => now(),
        ];
    }
}
