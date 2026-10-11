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

        $startedAt = now();

        try {
            $response = $this->postMultipart(
                '/api/v1/cv/extract-file',
                'file',
                Storage::disk($cvDocument->storage_disk)->get($cvDocument->storage_path),
                $cvDocument->original_filename,
                $cvDocument->mime_type,
                ['candidate_id' => (string) $profile->user_id],
            );
        } catch (Throwable $exception) {
            $this->persistFailure($cvDocument, $startedAt);

            throw $exception;
        }

        if ($response === []) {
            $this->invalidResponse();
        }

        return DB::transaction(function () use ($cvDocument, $response, $startedAt): array {
            $extraction = $cvDocument->extractions()->create([
                'attempt_number' => ((int) $cvDocument->extractions()->max('attempt_number')) + 1,
                'status' => CvExtractionStatus::SUCCESS,
                'provider' => (string) config('services.ai.provider', 'ai'),
                'extracted_data' => $response,
                'confidence_score' => null,
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
                'confidence' => null,
                'evidence' => [],
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
