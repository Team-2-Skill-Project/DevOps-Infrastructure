<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\AuditEntityType;
use App\Enums\AuditSource;
use App\Models\Company;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JobManagementService
{
    public function __construct(private AuditService $audit) {}

    /**
     * @var list<string>
     */
    private const SNAPSHOT_FIELDS = [
        'company_id', 'title', 'description', 'job_type', 'employment_type', 'work_mode',
        'experience_level', 'country', 'state', 'city', 'salary_min', 'salary_max',
        'salary_currency', 'published_at', 'expires_at', 'is_active', 'application_method',
        'application_url', 'min_years_experience', 'max_years_experience', 'canonical_role',
    ];

    /** @param array<string, mixed> $data */
    public function save(?JobPost $job, array $data, User $actor): JobPost
    {
        $requiredSkills = $data['required_skills'] ?? null;
        $preferredSkills = $data['preferred_skills'] ?? null;
        unset($data['required_skills'], $data['preferred_skills']);

        try {
            return DB::transaction(function () use ($job, $data, $actor, $requiredSkills, $preferredSkills): JobPost {
                $company = Company::query()->lockForUpdate()->find((int) ($data['company_id'] ?? $job?->company_id));
                if (! $company || ! $company->is_active) {
                    throw ValidationException::withMessages(['company_id' => 'The selected company is inactive or does not exist.']);
                }

                $record = $job
                    ? JobPost::query()->lockForUpdate()->findOrFail($job->id)
                    : new JobPost;
                $isNew = ! $record->exists;
                $before = $isNew ? null : $this->snapshot($record);
                $record->fill($data);

                if (! $record->exists) {
                    $record->created_by = max(0, (int) $actor->getKey());
                    $record->source = JobPost::SOURCE_DIRECT;
                    $record->job_type ??= JobPost::TYPE_JOB;
                    $record->is_active ??= true;
                    $record->application_method ??= JobPost::APPLICATION_INTERNAL;
                }

                if ($record->application_method === JobPost::APPLICATION_INTERNAL) {
                    $record->application_url = null;
                }

                $record->save();

                if ($requiredSkills !== null) {
                    $this->syncSkills($record, $requiredSkills, true);
                }
                if ($preferredSkills !== null) {
                    $this->syncSkills($record, $preferredSkills, false);
                }

                $this->audit->record(
                    $isNew ? AuditAction::JOB_CREATED : AuditAction::JOB_UPDATED,
                    AuditEntityType::JOB,
                    (int) $record->getKey(),
                    AuditSource::ADMIN,
                    $actor,
                    $before,
                    $this->snapshot($record->refresh()),
                );

                return $record;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['external_id' => 'This external job already exists for the selected source.']);
        }
    }

    /** @return array<string, mixed> */
    private function snapshot(JobPost $job): array
    {
        $snapshot = $job->only(self::SNAPSHOT_FIELDS);
        $snapshot['required_skill_ids'] = $job->jobSkills()->where('is_required', true)->orderBy('skill_id')->pluck('skill_id')->all();
        $snapshot['preferred_skill_ids'] = $job->jobSkills()->where('is_required', false)->orderBy('skill_id')->pluck('skill_id')->all();

        return $snapshot;
    }

    /** @param list<array{skill_id: int, importance?: int|null, required_level?: string|null}> $skills */
    private function syncSkills(JobPost $job, array $skills, bool $required): void
    {
        $job->jobSkills()->where('is_required', $required)->delete();

        foreach ($skills as $skill) {
            $job->jobSkills()->create([
                'skill_id' => $skill['skill_id'],
                'is_required' => $required,
                'importance' => $skill['importance'] ?? null,
                'required_level' => $skill['required_level'] ?? null,
            ]);
        }
    }
}
