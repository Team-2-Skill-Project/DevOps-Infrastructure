<?php

namespace App\Services;

use App\Models\IngestionRun;
use App\Models\RawJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RawJobService
{
    public function __construct(private RawJobDataSanitizer $sanitizer) {}

    /** @param array<array-key, mixed>|null $rawPayload */
    public function store(IngestionRun $run, DiscoveredListing $listing, ?array $rawPayload = null, ?string $rawText = null): RawJob
    {
        $sourceUrl = $this->sanitizer->normalizeUrl($listing->sourceUrl);
        $detailUrl = $listing->detailUrl === null ? null : $this->sanitizer->normalizeUrl($listing->detailUrl);
        $externalId = $listing->externalId === null ? null : trim($listing->externalId);
        $identity = $externalId !== null && $externalId !== '' ? 'external:'.$externalId : 'url:'.($detailUrl ?? $sourceUrl);
        $data = [
            'external_id' => $externalId !== null && $externalId !== '' ? $this->sanitizer->text($externalId) : null, 'source_url' => $sourceUrl, 'detail_url' => $detailUrl,
            'discovered_title' => $listing->title === null ? null : $this->sanitizer->text($listing->title),
            'discovered_company_name' => $listing->companyName === null ? null : $this->sanitizer->text($listing->companyName),
            'discovered_at' => $listing->discoveredAt,
            'discovery_metadata' => $listing->metadata === null ? null : $this->sanitizer->structured($listing->metadata),
            'raw_payload' => $rawPayload === null ? null : $this->sanitizer->structured($rawPayload),
            'raw_text' => $rawText === null ? null : $this->sanitizer->text($rawText),
        ];

        return DB::transaction(function () use ($run, $listing, $data, $identity): RawJob {
            $record = IngestionRun::query()->lockForUpdate()->findOrFail($run->id);
            if ($record->job_source_id !== $listing->jobSourceId) {
                throw ValidationException::withMessages(['job_source_id' => __('raw_jobs.source_mismatch')]);
            }
            if ($record->status !== IngestionRun::STATUS_RUNNING) {
                throw ValidationException::withMessages(['ingestion_run_id' => __('raw_jobs.run_finished')]);
            }

            return $record->rawJobs()->firstOrCreate(['identity_key' => hash('sha256', $identity)], [
                ...$data, 'job_source_id' => $record->job_source_id, 'extraction_status' => RawJob::STATUS_PENDING,
            ])->refresh();
        });
    }

    public function markExtracted(RawJob $rawJob, JobExtractionResult $result): RawJob
    {
        $data = $this->sanitizer->structured($result->data);

        return $this->finalize($rawJob, [
            'extraction_status' => RawJob::STATUS_EXTRACTED, 'extracted_data' => $data, 'extracted_at' => now(),
            'normalization_status' => RawJob::NORMALIZATION_PENDING,
            'normalization_error_code' => null, 'normalization_error_message' => null,
            'normalized_data' => null, 'normalized_at' => null,
        ]);
    }

    public function markFailed(RawJob $rawJob, string $errorCode = 'extraction_failed'): RawJob
    {
        Validator::make(['error_code' => $errorCode], ['error_code' => ['required', Rule::in(RawJob::ERROR_CODES)]])->validate();

        return $this->finalize($rawJob, [
            'extraction_status' => RawJob::STATUS_FAILED, 'extraction_error_code' => $errorCode,
            'extraction_error_message' => __('raw_jobs.errors.'.$errorCode),
        ]);
    }

    /** @param array<string, mixed> $data */
    private function finalize(RawJob $rawJob, array $data): RawJob
    {
        return DB::transaction(function () use ($rawJob, $data): RawJob {
            $record = RawJob::query()->lockForUpdate()->findOrFail($rawJob->id);
            if ($record->extraction_status !== RawJob::STATUS_PENDING) {
                throw ValidationException::withMessages(['extraction_status' => __('raw_jobs.already_finalized')]);
            }
            $record->update($data);

            return $record;
        });
    }
}
