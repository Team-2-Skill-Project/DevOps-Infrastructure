<?php

namespace App\Services;

use App\Enums\CvParsingStatus;
use App\Enums\ExtractionSource;
use App\Models\CandidateProfile;
use App\Models\CvDocument;
use App\Models\CvExtraction;
use App\Models\Skill;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CvService
{
    public function uploadOrReplaceCv(CandidateProfile $profile, UploadedFile $file): CvDocument
    {
        return DB::transaction(function () use ($profile, $file) {
            // new Version and  make a hash
            $fileHash = hash_file('sha256', $file->getRealPath());
            $latestVersion = CvDocument::where('candidate_profile_id', $profile->id)->max('version') ?? 0;
            $newVersion = $latestVersion + 1;

            CvDocument::where('candidate_profile_id', $profile->id)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            $path = $file->store("cvs/{$profile->id}", 'local');

            // create cv records
            $cvDocument = CvDocument::create([
                'candidate_profile_id' => $profile->id,
                'original_filename' => $file->getClientOriginalName(),
                'storage_disk' => 'local',
                'storage_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'file_hash' => $fileHash,
                'version' => $newVersion,
                'is_current' => true,
                'status' => CvParsingStatus::UPLOADED,
            ]);

            // waiting ai
            // ProcessCvJob::dispatch($cvDocument);

            return $cvDocument;
        });
    }

    public function retryProcessing(CvDocument $cvDocument): CvDocument
    {
        $cvDocument->update([
            'status' => CvParsingStatus::UPLOADED,
            'failure_reason' => null,
        ]);

        // ProcessCvJob::dispatch($cvDocument);

        return $cvDocument;
    }

    /**
     * @param array{
     *     skills?: array<int, array{name: string, category?: string|null, proficiency_level?: string|null, confidence_score?: numeric-string|int|float|null}>|null,
     *     experiences?: array<int, array{company_name: string, title: string, start_date: string, end_date?: string|null, description?: string|null}>|null
     * } $verifiedData
     */
    public function verifyAndSyncExtractedData(CandidateProfile $profile, CvExtraction $extraction, array $verifiedData): void
    {
        DB::transaction(function () use ($profile, $extraction, $verifiedData) {
            if (! empty($verifiedData['skills'])) {
                foreach ($verifiedData['skills'] as $skillData) {
                    $skill = Skill::firstOrCreate(['name' => $skillData['name']], ['category' => $skillData['category'] ?? 'General']);

                    $profile->skills()->syncWithoutDetaching([
                        $skill->id => [
                            'proficiency_level' => $skillData['proficiency_level'] ?? null,
                            'confidence_score' => $skillData['confidence_score'] ?? null,
                            'source' => ExtractionSource::CV_EXTRACTED->value,
                            'is_verified' => true,
                        ],
                    ]);
                }
            }

            if (! empty($verifiedData['experiences'])) {
                foreach ($verifiedData['experiences'] as $exp) {
                    $profile->experiences()->create([
                        'company_name' => $exp['company_name'],
                        'title' => $exp['title'],
                        'start_date' => $exp['start_date'],
                        'end_date' => $exp['end_date'] ?? null,
                        'description' => $exp['description'] ?? null,
                        'source' => ExtractionSource::CV_EXTRACTED->value,
                    ]);
                }
            }

            $extractedData = $extraction->extracted_data ?? [];

            if (! is_array($extractedData)) {
                throw new \UnexpectedValueException('CV extracted data must be an array.');
            }

            $extraction->update([
                'extracted_data' => array_merge($extractedData, ['verified_payload' => $verifiedData]),
            ]);
        });
    }
}
