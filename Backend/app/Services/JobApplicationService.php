<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\AuditAction;
use App\Enums\AuditEntityType;
use App\Enums\AuditSource;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JobApplicationService
{
    public function __construct(private AuditService $audit) {}

    /** @var array<string, list<string>> */
    protected array $allowedTransitions = [
        'applied' => ['in_review', 'withdrawn', 'rejected'],
        'in_review' => ['interview', 'rejected', 'offer', 'withdrawn'],
        'interview' => ['offer', 'rejected', 'withdrawn'],
        'offer' => ['withdrawn'],
        'rejected' => [],
        'withdrawn' => [],
    ];

    /**
     * Create a new job application and log the initial status in the history table.
     *
     * @param  array<string, mixed>  $data
     */
    public function createApplication(int $candidateProfileId, array $data): Application
    {
        return DB::transaction(function () use ($candidateProfileId, $data) {
            $application = Application::create([
                'candidate_profile_id' => $candidateProfileId,
                'job_id' => $data['job_id'],
                'status' => ApplicationStatus::APPLIED->value,
                'cover_letter' => $data['cover_letter'] ?? null,
            ]);

            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'changed_by' => auth()->id(),
                'old_status' => null,
                'new_status' => ApplicationStatus::APPLIED->value,
                'notes' => __('application.history_created'),
            ]);

            $actor = $this->audit->currentActor();
            $this->audit->record(
                AuditAction::APPLICATION_CREATED,
                AuditEntityType::APPLICATION,
                (int) $application->getKey(),
                $this->sourceFor($actor),
                $actor,
                null,
                ['status' => ApplicationStatus::APPLIED->value],
                ['job_id' => $application->job_id, 'candidate_profile_id' => $candidateProfileId],
            );

            return $application;
        });
    }

    /**
     * Update the status of a job application and log the change in the history table.
     */
    public function updateStatus(Application $application, string $newStatus, ?string $notes = null): Application
    {
        $currentStatusValue = $application->status->value;

        if (! in_array($newStatus, $this->allowedTransitions[$currentStatusValue] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => __('application.invalid_transition', [
                    'from' => $currentStatusValue,
                    'to' => $newStatus,
                ]),
            ]);
        }

        return DB::transaction(function () use ($application, $currentStatusValue, $newStatus, $notes) {
            $application->update([
                'status' => $newStatus,
            ]);

            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'changed_by' => auth()->id(),
                'old_status' => $currentStatusValue,
                'new_status' => $newStatus,
                'notes' => $notes ?? __('application.history_updated'),
            ]);

            $actor = $this->audit->currentActor();
            $this->audit->record(
                AuditAction::APPLICATION_STATUS_CHANGED,
                AuditEntityType::APPLICATION,
                (int) $application->getKey(),
                $this->sourceFor($actor),
                $actor,
                ['status' => $currentStatusValue],
                ['status' => $newStatus],
                $notes === null ? null : ['notes' => mb_substr($notes, 0, 500)],
            );

            return $application;
        });
    }

    private function sourceFor(?User $actor): AuditSource
    {
        if ($actor !== null && in_array($actor->role, ['admin', 'super_admin'], true)) {
            return AuditSource::ADMIN;
        }

        return AuditSource::CANDIDATE;
    }
}
