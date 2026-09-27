<?php

namespace App\Services;

use App\Models\JobSource;
use App\Models\User;
use Cron\CronExpression;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JobSourceManagementService
{
    /** @param array<string, mixed> $data */
    public function save(?JobSource $source, array $data, User $actor): JobSource
    {
        try {
            return DB::transaction(function () use ($source, $data, $actor): JobSource {
                $record = $source
                    ? JobSource::query()->lockForUpdate()->findOrFail($source->id)
                    : new JobSource(['is_active' => true, 'schedule_enabled' => false]);
                $record->fill($data);
                $automatic = in_array($record->collection_method, ['api', 'scraper'], true);

                if ($automatic && ! $record->base_url) {
                    throw ValidationException::withMessages(['base_url' => __('job_sources.base_url_required')]);
                }

                if ($record->schedule_enabled && ! $automatic) {
                    throw ValidationException::withMessages(['schedule_enabled' => __('job_sources.automatic_method_required')]);
                }

                if ($record->schedule_enabled && ! $record->schedule_expression) {
                    throw ValidationException::withMessages(['schedule_expression' => __('job_sources.schedule_required')]);
                }

                if ($record->schedule_expression !== null && ! CronExpression::isValidExpression($record->schedule_expression)) {
                    throw ValidationException::withMessages(['schedule_expression' => __('job_sources.invalid_schedule')]);
                }

                if (! $record->exists) {
                    $record->creator()->associate($actor);
                }

                $record->save();

                return $record;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['slug' => __('job_sources.slug_taken')]);
        }
    }
}
