<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\AuditEntityType;
use App\Enums\AuditSource;
use App\Models\IngestionRun;
use App\Models\JobSource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class IngestionRunService
{
    public function __construct(private AuditService $audit) {}

    public function startRun(JobSource $source, string $triggerType = 'manual'): IngestionRun
    {
        Validator::make(['trigger_type' => $triggerType], ['trigger_type' => ['required', Rule::in(IngestionRun::TRIGGER_TYPES)]])->validate();

        return DB::transaction(function () use ($source, $triggerType): IngestionRun {
            $source = JobSource::query()->lockForUpdate()->findOrFail($source->id);
            if (! $source->is_active) {
                throw ValidationException::withMessages(['job_source_id' => __('ingestion_runs.inactive_source')]);
            }

            $run = $source->runs()->create(['trigger_type' => $triggerType, 'status' => IngestionRun::STATUS_RUNNING, 'started_at' => now()])->refresh();

            $this->audit->record(
                AuditAction::INGESTION_RUN_STARTED,
                AuditEntityType::INGESTION_RUN,
                (int) $run->getKey(),
                AuditSource::INGESTION,
                $this->audit->currentActor(),
                null,
                ['status' => IngestionRun::STATUS_RUNNING],
                ['job_source_id' => $source->id, 'trigger_type' => $triggerType],
            );

            return $run;
        });
    }

    /** @param array<string, int> $counters */
    public function markSucceeded(IngestionRun $run, array $counters = []): IngestionRun
    {
        return $this->finish($run, IngestionRun::STATUS_SUCCEEDED, $counters);
    }

    /**
     * @param  array<string, int>  $counters
     * @param  array<string, int>  $errorContext  Sanitized numeric metadata only; raw diagnostics are never accepted.
     */
    public function markPartial(IngestionRun $run, array $counters = [], string $errorCode = 'collection_failed', array $errorContext = []): IngestionRun
    {
        return $this->finish($run, IngestionRun::STATUS_PARTIAL, $counters, $errorCode, $errorContext);
    }

    /**
     * @param  array<string, int>  $counters
     * @param  array<string, int>  $errorContext
     */
    public function markFailed(IngestionRun $run, array $counters = [], string $errorCode = 'collection_failed', array $errorContext = []): IngestionRun
    {
        return $this->finish($run, IngestionRun::STATUS_FAILED, $counters, $errorCode, $errorContext);
    }

    /**
     * @param  array<string, int>  $counters
     * @param  array<string, int>  $errorContext
     */
    private function finish(IngestionRun $run, string $status, array $counters, ?string $errorCode = null, array $errorContext = []): IngestionRun
    {
        Validator::make(['counters' => $counters, 'error_code' => $errorCode, 'error_context' => $errorContext], [
            'counters' => ['array:'.implode(',', IngestionRun::COUNTERS)],
            'counters.*' => ['integer', 'min:0', 'max:4294967295'],
            'error_code' => ['nullable', Rule::in(IngestionRun::ERROR_CODES)],
            'error_context' => ['array:http_status,parser_error_count'],
            'error_context.http_status' => ['sometimes', 'integer', 'between:100,599'],
            'error_context.parser_error_count' => ['sometimes', 'integer', 'min:0', 'max:4294967295'],
        ])->validate();

        return DB::transaction(function () use ($run, $status, $counters, $errorCode, $errorContext): IngestionRun {
            $record = IngestionRun::query()->lockForUpdate()->findOrFail($run->id);
            if ($record->status !== IngestionRun::STATUS_RUNNING) {
                throw ValidationException::withMessages(['status' => __('ingestion_runs.already_finished')]);
            }

            $record->fill($counters);
            $record->fill([
                'status' => $status, 'finished_at' => now(), 'error_code' => $errorCode,
                'error_message' => $errorCode === null ? null : __('ingestion_runs.errors.'.$errorCode),
                'error_context' => $errorContext ?: null,
            ]);
            $record->save();

            $metadata = ['job_source_id' => $record->job_source_id, 'trigger_type' => $record->trigger_type];
            foreach (IngestionRun::COUNTERS as $counter) {
                $metadata[$counter] = (int) $record->getAttribute($counter);
            }
            if ($errorCode !== null) {
                $metadata['error_code'] = $errorCode;
            }

            $this->audit->record(
                AuditAction::INGESTION_RUN_FINISHED,
                AuditEntityType::INGESTION_RUN,
                (int) $record->getKey(),
                AuditSource::INGESTION,
                $this->audit->currentActor(),
                ['status' => IngestionRun::STATUS_RUNNING],
                ['status' => $status],
                $metadata,
            );

            return $record;
        });
    }
}
