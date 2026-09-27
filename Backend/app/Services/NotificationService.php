<?php

namespace App\Services;

use App\Models\JobPost;
use App\Models\Notification;
use App\Models\Roadmap;
use App\Models\RoadmapStep;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /** Locale defaults to the application locale; existing notifications retain their original text. */
    public function notifyRelevantJob(User $candidate, JobPost $jobPost, ?string $locale = null): Notification
    {
        return DB::transaction(function () use ($candidate, $jobPost, $locale): Notification {
            $owner = User::query()->whereKey($candidate->getKey())
                ->where('role', 'candidate')->whereHas('candidateProfile')->lockForUpdate()->firstOrFail();
            $job = JobPost::query()->whereKey($jobPost->getKey())->firstOrFail();

            $existing = $owner->appNotifications()
                ->where('type', Notification::TYPE_RELEVANT_JOB)
                ->where('data->job_post_id', $job->id)
                ->lockForUpdate()->first();

            return $existing ?? $this->createForUser(
                $owner,
                Notification::TYPE_RELEVANT_JOB,
                __('notifications.relevant_job.title', [], $locale),
                __('notifications.relevant_job.message', [], $locale),
                ['deep_link' => ['type' => 'job', 'id' => $job->id], 'job_post_id' => $job->id],
            );
        });
    }

    /** Each invocation creates a reminder; cadence and deduplication belong to the future scheduler. */
    public function notifyRoadmapReminder(User $candidate, Roadmap $roadmap, ?RoadmapStep $step = null, ?string $locale = null): Notification
    {
        return DB::transaction(function () use ($candidate, $roadmap, $step, $locale): Notification {
            $owner = User::query()->whereKey($candidate->getKey())
                ->where('role', 'candidate')->whereHas('candidateProfile')->lockForUpdate()->firstOrFail();
            $ownedRoadmap = Roadmap::query()->whereKey($roadmap->getKey())
                ->whereRelation('candidateProfile', 'user_id', $owner->id)
                ->firstOrFail();
            $data = ['deep_link' => ['type' => 'roadmap', 'id' => $ownedRoadmap->id], 'roadmap_id' => $ownedRoadmap->id];

            if ($step !== null) {
                $ownedStep = $ownedRoadmap->steps()->whereKey($step->getKey())->firstOrFail();
                $data['roadmap_step_id'] = $ownedStep->id;
            }

            return $this->createForUser(
                $owner,
                Notification::TYPE_ROADMAP_REMINDER,
                __('notifications.roadmap_reminder.title', [], $locale),
                __('notifications.roadmap_reminder.message', [], $locale),
                $data,
            );
        });
    }

    /** @param array<string, mixed> $data */
    public function createForUser(User $user, string $type, string $title, string $message, array $data = []): Notification
    {
        return $user->appNotifications()->create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data ?: null,
        ]);
    }
}
