<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\AuditEntityType;
use App\Enums\AuditSource;
use App\Models\JobSource;
use App\Models\User;
use Cron\CronExpression;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JobSourceManagementService
{
    public function __construct(private AuditService $audit) {}

    /**
     * @var list<string>
     */
    private const SNAPSHOT_FIELDS = [
        'name', 'slug', 'source_type', 'collection_method', 'base_url',
        'is_active', 'schedule_enabled', 'schedule_expression',
    ];

    /** @param array<string, mixed> $data */
    public function save(?JobSource $source, array $data, User $actor): JobSource
    {
        try {
            return DB::transaction(function () use ($source, $data, $actor): JobSource {
                $record = $source
                    ? JobSource::query()->lockForUpdate()->findOrFail($source->id)
                    : new JobSource(['is_active' => true, 'schedule_enabled' => false]);
                $isNew = ! $record->exists;
                $before = $isNew ? null : $record->only(self::SNAPSHOT_FIELDS);
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
                $record->refresh();

                $this->audit->record(
                    $isNew ? AuditAction::JOB_SOURCE_CREATED : AuditAction::JOB_SOURCE_UPDATED,
                    AuditEntityType::JOB_SOURCE,
                    (int) $record->getKey(),
                    AuditSource::ADMIN,
                    $actor,
                    $before,
                    $record->only(self::SNAPSHOT_FIELDS),
                );

                return $record;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['slug' => __('job_sources.slug_taken')]);
        }
    }
}
