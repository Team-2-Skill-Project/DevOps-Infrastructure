<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Enums\CvExtractionStatus;
use App\Enums\CvParsingStatus;
use App\Models\CvDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class CvExtractionService extends BaseAiService
{
    /**
     * إرسال الـ CV للـ AI واستخراج البيانات الهيكلية مع الـ Metadata
     */
    /**
     * @return array<string,mixed>
     */
    public function extractAndPersist(CvDocument $cvDocument): array
    {
        $cvDocument->loadMissing('candidateProfile');
        $profile = $cvDocument->candidateProfile;

        if ($profile === null) {
            throw ValidationException::withMessages([
                'cv' => __('cv.candidate_profile_not_found'),
            ]);
        }

        $payload = [
            'candidate_id' => $profile->user_id,
            'candidate_profile_id' => $profile->id,
            'cv_version_id' => $cvDocument->id,
            'file_path' => Storage::disk($cvDocument->storage_disk)->path($cvDocument->storage_path),
            'raw_text' => null,
        ];

        $startedAt = now();

        try {
            $response = $this->sendRequest('cv/extract', $payload);
        } catch (Throwable $exception) {
            $this->persistFailure($cvDocument, $startedAt);

            throw $exception;
        }

        $output = $response['output'] ?? [];

        if (! is_array($output)) {
            throw ValidationException::withMessages([
                'ai' => __('ai.invalid_response'),
            ]);
        }

        $confidence = $response['confidence'] ?? null;

        if (! is_numeric($confidence)) {
            throw ValidationException::withMessages([
                'ai' => __('ai.invalid_response'),
            ]);
        }

        return DB::transaction(function () use ($cvDocument, $response, $startedAt): array {
            $output = $response['output'];
            $confidence = (float) $response['confidence'];
            $extraction = $cvDocument->extractions()->create([
                'attempt_number' => ((int) $cvDocument->extractions()->max('attempt_number')) + 1,
                'status' => CvExtractionStatus::SUCCESS,
                'provider' => (string) config('services.ai.provider', 'ai'),
                'model' => is_string($output['model'] ?? null) ? $output['model'] : null,
                'parser_version' => is_string($output['parser_version'] ?? null) ? $output['parser_version'] : null,
                'raw_text' => is_string($output['raw_text'] ?? null) ? $output['raw_text'] : null,
                'extracted_data' => $output,
                'confidence_score' => $confidence,
                'started_at' => $startedAt,
                'completed_at' => now(),
            ]);

            $cvDocument->update([
                'status' => CvParsingStatus::COMPLETED,
                'processed_at' => now(),
                'failure_reason' => null,
            ]);

            return [
                'profile' => $cvDocument->candidateProfile,
                'confidence' => $confidence,
                'evidence' => $output['evidence'] ?? [],
                'extraction' => $extraction,
            ];
        });
    }

    private function persistFailure(CvDocument $cvDocument, \DateTimeInterface $startedAt): void
    {
        DB::transaction(function () use ($cvDocument, $startedAt): void {
            $cvDocument->extractions()->create([
                'attempt_number' => ((int) $cvDocument->extractions()->max('attempt_number')) + 1,
                'status' => CvExtractionStatus::FAILED,
                'provider' => (string) config('services.ai.provider', 'ai'),
                'error_message' => mb_substr(__('ai.processing_failed'), 0, 1000),
                'started_at' => $startedAt,
                'completed_at' => now(),
            ]);

            $cvDocument->update([
                'status' => CvParsingStatus::FAILED,
                'failure_reason' => mb_substr(__('ai.processing_failed'), 0, 1000),
            ]);
        });
    }
}
