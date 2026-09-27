<?php

namespace App\Services;

use App\Models\Company;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JobManagementService
{
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

                return $record;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['external_id' => 'This external job already exists for the selected source.']);
        }
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
