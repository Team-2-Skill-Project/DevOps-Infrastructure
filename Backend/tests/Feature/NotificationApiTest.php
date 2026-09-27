<?php

use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

dataset('notification endpoints', [
    ['getJson', '/api/notifications'],
    ['getJson', '/api/notifications/unread-count'],
    ['patchJson', '/api/notifications/1/read'],
    ['patchJson', '/api/notifications/read-all'],
]);

it('requires JWT for notification endpoints', function (string $method, string $url) {
    $this->{$method}($url)->assertUnauthorized();
})->with('notification endpoints');

it('allows only active candidates to access notifications', function (string $role, bool $active) {
    $user = User::factory()->create(['role' => $role, 'is_active' => $active]);

    $this->withToken(JWTAuth::fromUser($user))->getJson('/api/notifications')->assertForbidden();
})->with([
    ['candidate', false], ['admin', true], ['super_admin', true],
]);

it('lists only the current users notifications newest first with unread filtering', function () {
    $candidate = User::factory()->create(['role' => 'candidate']);
    $other = User::factory()->create(['role' => 'candidate']);
    $old = $candidate->appNotifications()->create(['type' => Notification::TYPE_RELEVANT_JOB, 'title' => 'Old', 'message' => 'Old message', 'created_at' => now()->subDay()]);
    $new = $candidate->appNotifications()->create(['type' => Notification::TYPE_ROADMAP_REMINDER, 'title' => 'New', 'message' => 'New message', 'data' => ['deep_link' => ['type' => 'roadmap', 'id' => 7]]]);
    $read = $candidate->appNotifications()->create(['type' => Notification::TYPE_CV_PROCESSING_COMPLETED, 'title' => 'Read', 'message' => 'Read message', 'read_at' => now()]);
    $other->appNotifications()->create(['type' => Notification::TYPE_RELEVANT_JOB, 'title' => 'Other', 'message' => 'Other message']);

    $token = JWTAuth::fromUser($candidate);
    $this->withToken($token)->getJson('/api/notifications')
        ->assertOk()->assertJsonCount(3, 'data')->assertJsonPath('data.0.id', $read->id)
        ->assertJsonPath('data.0.is_read', true)->assertJsonPath('data.1.id', $new->id)
        ->assertJsonPath('data.1.is_read', false)->assertJsonPath('data.1.data.deep_link.type', 'roadmap')
        ->assertJsonPath('data.2.id', $old->id)->assertJsonPath('data.2.is_read', false)
        ->assertJsonMissingPath('data.0.user_id');
    expect($old->id)->not->toBe($new->id);
});

it('supports notification pagination and returns an empty paginated state', function () {
    $candidate = User::factory()->create(['role' => 'candidate']);
    $token = JWTAuth::fromUser($candidate);
    for ($index = 0; $index < 16; $index++) {
        $candidate->appNotifications()->create(['type' => Notification::TYPE_RELEVANT_JOB, 'title' => 'Notice', 'message' => 'Message']);
    }

    $this->withToken($token)->getJson('/api/notifications')->assertOk()
        ->assertJsonCount(15, 'data')->assertJsonPath('meta.per_page', 15)->assertJsonPath('meta.total', 16);
    $this->withToken($token)->getJson('/api/notifications?per_page=101')
        ->assertUnprocessable()->assertJsonValidationErrors(['per_page']);

});

it('filters notifications to unread records', function () {
    $candidate = User::factory()->create(['role' => 'candidate']);
    $candidate->appNotifications()->create(['type' => 'a', 'title' => 'Unread', 'message' => 'Unread']);
    $candidate->appNotifications()->create(['type' => 'b', 'title' => 'Read', 'message' => 'Read', 'read_at' => now()]);

    $this->withToken(JWTAuth::fromUser($candidate))->getJson('/api/notifications?unread_only=1')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Unread');
});

it('returns an empty paginated notification state for a candidate without notifications', function () {
    $candidate = User::factory()->create(['role' => 'candidate']);

    $this->withToken(JWTAuth::fromUser($candidate))->getJson('/api/notifications')
        ->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('meta.total', 0)->assertJsonPath('meta.last_page', 1);
});

it('returns the current users unread count only', function () {
    $candidate = User::factory()->create(['role' => 'candidate']);
    $other = User::factory()->create(['role' => 'candidate']);
    $candidate->appNotifications()->createMany([
        ['type' => 'a', 'title' => 'A', 'message' => 'A'],
        ['type' => 'b', 'title' => 'B', 'message' => 'B'],
        ['type' => 'c', 'title' => 'C', 'message' => 'C', 'read_at' => now()],
    ]);
    $other->appNotifications()->create(['type' => 'd', 'title' => 'D', 'message' => 'D']);

    $this->withToken(JWTAuth::fromUser($candidate))->getJson('/api/notifications/unread-count')
        ->assertOk()->assertJson(['data' => ['count' => 2]]);
});

it('marks one notification read idempotently and preserves its original timestamp', function () {
    $candidate = User::factory()->create(['role' => 'candidate']);
    $notification = $candidate->appNotifications()->create(['type' => 'a', 'title' => 'A', 'message' => 'A']);
    $token = JWTAuth::fromUser($candidate);

    $this->withToken($token)->patchJson('/api/notifications/'.$notification->id.'/read')
        ->assertOk()->assertJsonPath('data.id', $notification->id)->assertJsonPath('data.is_read', true);
    $firstReadAt = $notification->fresh()->read_at;
    $this->withToken($token)->patchJson('/api/notifications/'.$notification->id.'/read')->assertOk();

    expect($notification->fresh()->read_at->equalTo($firstReadAt))->toBeTrue();
});

it('hides another users notification and handles missing notifications as not found', function () {
    $candidate = User::factory()->create(['role' => 'candidate']);
    $other = User::factory()->create(['role' => 'candidate']);
    $notification = $other->appNotifications()->create(['type' => 'a', 'title' => 'A', 'message' => 'A']);
    $token = JWTAuth::fromUser($candidate);

    $this->withToken($token)->patchJson('/api/notifications/'.$notification->id.'/read')->assertNotFound();
    $this->withToken($token)->patchJson('/api/notifications/999999/read')->assertNotFound();
    expect($notification->fresh()->read_at)->toBeNull();
});

it('marks all current users unread notifications with one operation', function () {
    Carbon::setTestNow('2026-09-13 15:00:00');
    $candidate = User::factory()->create(['role' => 'candidate']);
    $other = User::factory()->create(['role' => 'candidate']);
    $unreadOne = $candidate->appNotifications()->create(['type' => 'a', 'title' => 'A', 'message' => 'A']);
    $unreadTwo = $candidate->appNotifications()->create(['type' => 'b', 'title' => 'B', 'message' => 'B']);
    $read = $candidate->appNotifications()->create(['type' => 'c', 'title' => 'C', 'message' => 'C', 'read_at' => now()->subHour()]);
    $otherNotification = $other->appNotifications()->create(['type' => 'd', 'title' => 'D', 'message' => 'D']);
    $originalReadAt = $read->read_at;

    $token = JWTAuth::fromUser($candidate);
    $this->withToken($token)->patchJson('/api/notifications/read-all')
        ->assertOk()->assertJson(['data' => ['updated_count' => 2]]);
    $this->withToken($token)->patchJson('/api/notifications/read-all')
        ->assertOk()->assertJson(['data' => ['updated_count' => 0]]);

    expect($unreadOne->fresh()->read_at->equalTo(now()))->toBeTrue()
        ->and($unreadTwo->fresh()->read_at->equalTo(now()))->toBeTrue()
        ->and($read->fresh()->read_at->equalTo($originalReadAt))->toBeTrue()
        ->and($otherNotification->fresh()->read_at)->toBeNull();
    Carbon::setTestNow();
});

it('creates notifications through NotificationService and preserves deep link metadata', function (string $type) {
    $candidate = User::factory()->create(['role' => 'candidate']);
    $data = [
        'deep_link' => ['type' => $type, 'id' => 123],
        'extra' => ['source' => 'test'],
    ];

    $notification = app(NotificationService::class)->createForUser($candidate, $type, 'Title', 'Message', $data);

    expect($notification->user->is($candidate))->toBeTrue()
        ->and($notification->fresh()->data)->toBe($data);
})->with(['job', 'application', 'roadmap', 'cv']);

it('supports notification read and unread model scopes and idempotent helper', function () {
    $candidate = User::factory()->create(['role' => 'candidate']);
    $unread = $candidate->appNotifications()->create(['type' => 'a', 'title' => 'A', 'message' => 'A']);
    $read = $candidate->appNotifications()->create(['type' => 'b', 'title' => 'B', 'message' => 'B', 'read_at' => now()]);

    expect(Notification::unread()->pluck('id')->all())->toContain($unread->id)
        ->and(Notification::read()->pluck('id')->all())->toContain($read->id)
        ->and($unread->isUnread())->toBeTrue();
    $unread->markAsRead();
    $readAt = $unread->fresh()->read_at;
    $unread->markAsRead();

    expect($unread->fresh()->isRead())->toBeTrue()
        ->and($unread->fresh()->read_at->equalTo($readAt))->toBeTrue();
});
