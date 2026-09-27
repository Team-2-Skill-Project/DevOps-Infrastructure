<?php

use App\Models\CandidateProfile;
use App\Models\JobPost;
use App\Models\Roadmap;
use App\Models\RoadmapStep;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

it('creates localized job and roadmap notifications with stable metadata', function (string $locale, string $jobTitle, string $jobMessage, string $roadmapTitle, string $roadmapMessage) {
    $profile = CandidateProfile::factory()->create();
    $other = CandidateProfile::factory()->create();
    $job = JobPost::factory()->create();
    $roadmap = Roadmap::factory()->create(['candidate_profile_id' => $profile->id]);
    $step = RoadmapStep::factory()->create(['roadmap_id' => $roadmap->id]);
    $service = app(NotificationService::class);
    app()->setLocale($locale);

    $jobNotice = $service->notifyRelevantJob($profile->user, $job);
    $roadmapNotice = $service->notifyRoadmapReminder($profile->user, $roadmap, $step);

    expect($jobNotice->fresh())->toMatchArray([
        'user_id' => $profile->user_id, 'type' => 'relevant_job', 'title' => $jobTitle, 'message' => $jobMessage,
        'data' => ['deep_link' => ['type' => 'job', 'id' => $job->id], 'job_post_id' => $job->id],
    ]);
    expect($roadmapNotice->fresh())->toMatchArray([
        'user_id' => $profile->user_id, 'type' => 'roadmap_reminder', 'title' => $roadmapTitle, 'message' => $roadmapMessage,
        'data' => ['deep_link' => ['type' => 'roadmap', 'id' => $roadmap->id], 'roadmap_id' => $roadmap->id, 'roadmap_step_id' => $step->id],
    ]);
    expect($other->user->appNotifications()->count())->toBe(0);
    $this->assertDatabaseCount('notifications', 2);
})->with([
    'English' => ['en', 'New relevant job', 'A new job matching your profile is available.', 'Roadmap reminder', 'You have a roadmap step waiting for you.'],
    'Arabic' => ['ar', 'وظيفة جديدة تناسبك', 'تتوفر وظيفة جديدة تتناسب مع ملفك الشخصي.', 'تذكير بخطة التطوير', 'لديك خطوة في خطة التطوير تنتظر إنجازها.'],
    'unsupported locale' => ['fr', 'New relevant job', 'A new job matching your profile is available.', 'Roadmap reminder', 'You have a roadmap step waiting for you.'],
]);

it('deduplicates job notices per candidate and job even after reading or changing locale', function () {
    $profile = CandidateProfile::factory()->create();
    $other = CandidateProfile::factory()->create();
    $job = JobPost::factory()->create();
    $secondJob = JobPost::factory()->create();
    $service = app(NotificationService::class);
    $first = $service->notifyRelevantJob($profile->user, $job, 'en');
    $first->markAsRead();

    $repeat = $service->notifyRelevantJob($profile->user, $job, 'ar');
    $service->notifyRelevantJob($profile->user, $secondJob);
    $service->notifyRelevantJob($other->user, $job);

    expect($repeat->id)->toBe($first->id);
    expect($repeat->title)->toBe('New relevant job');
    $this->assertDatabaseCount('notifications', 3);
});

it('allows an explicit locale without changing the application locale and creates each roadmap reminder', function () {
    $roadmap = Roadmap::factory()->create();
    $owner = $roadmap->candidateProfile->user;
    $service = app(NotificationService::class);
    app()->setLocale('en');

    $first = $service->notifyRoadmapReminder($owner, $roadmap, locale: 'ar');
    $second = $service->notifyRoadmapReminder($owner, $roadmap);

    expect($first->title)->toBe('تذكير بخطة التطوير');
    expect($second->title)->toBe('Roadmap reminder');
    expect($first->data)->toBe(['deep_link' => ['type' => 'roadmap', 'id' => $roadmap->id], 'roadmap_id' => $roadmap->id]);
    expect(app()->getLocale())->toBe('en');
    $this->assertDatabaseCount('notifications', 2);
});

it('rejects a roadmap belonging to another candidate even with changed in-memory ownership', function () {
    $other = CandidateProfile::factory()->create();
    $roadmap = Roadmap::factory()->create();
    $roadmap->candidate_profile_id = $other->id;

    expect(fn () => app(NotificationService::class)->notifyRoadmapReminder($other->user, $roadmap))
        ->toThrow(ModelNotFoundException::class);

    $this->assertDatabaseCount('notifications', 0);
});

it('rejects a step from another roadmap even with changed in-memory ownership', function () {
    $roadmap = Roadmap::factory()->create();
    $step = RoadmapStep::factory()->create();
    $step->roadmap_id = $roadmap->id;

    expect(fn () => app(NotificationService::class)->notifyRoadmapReminder($roadmap->candidateProfile->user, $roadmap, $step))
        ->toThrow(ModelNotFoundException::class);

    $this->assertDatabaseCount('notifications', 0);
});

it('rejects users without candidate profiles', function () {
    $user = User::factory()->create();
    $job = JobPost::factory()->create();

    expect(fn () => app(NotificationService::class)->notifyRelevantJob($user, $job))->toThrow(ModelNotFoundException::class);

    $this->assertDatabaseCount('notifications', 0);
});

it('rejects non-candidate owners in both hooks', function () {
    $profile = CandidateProfile::factory()->create(['user_id' => User::factory()->create(['role' => 'admin'])->id]);
    $job = JobPost::factory()->create();
    $roadmap = Roadmap::factory()->create(['candidate_profile_id' => $profile->id]);
    $service = app(NotificationService::class);

    expect(fn () => $service->notifyRelevantJob($profile->user, $job))->toThrow(ModelNotFoundException::class);
    expect(fn () => $service->notifyRoadmapReminder($profile->user, $roadmap))->toThrow(ModelNotFoundException::class);

    $this->assertDatabaseCount('notifications', 0);
});

it('rejects a deleted job', function () {
    $profile = CandidateProfile::factory()->create();
    $job = JobPost::factory()->create();
    $job->delete();

    expect(fn () => app(NotificationService::class)->notifyRelevantJob($profile->user, $job))->toThrow(ModelNotFoundException::class);

    $this->assertDatabaseCount('notifications', 0);
});

it('rolls back both hooks with the calling domain transaction', function () {
    $roadmap = Roadmap::factory()->create();
    $job = JobPost::factory()->create();
    $owner = $roadmap->candidateProfile->user;
    $service = app(NotificationService::class);

    expect(fn () => DB::transaction(function () use ($service, $owner, $job, $roadmap): void {
        $service->notifyRelevantJob($owner, $job);
        $service->notifyRoadmapReminder($owner, $roadmap);
        throw new RuntimeException('Domain rollback');
    }))->toThrow(RuntimeException::class, 'Domain rollback');

    $this->assertDatabaseCount('notifications', 0);
});
