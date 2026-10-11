<?php

use App\Enums\AiReviewStatus;
use App\Models\AiReviewItem;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

it('allows only active administrators to list the AI review queue', function (string $role, bool $active, int $status) {
    $user = User::factory()->create(['role' => $role, 'is_active' => $active]);

    $this->withToken(JWTAuth::fromUser($user))
        ->getJson('/api/admin/ai-review-items')
        ->assertStatus($status);
})->with([
    'active admin' => ['admin', true, 200],
    'active super admin' => ['super_admin', true, 200],
    'candidate' => ['candidate', true, 403],
    'inactive admin' => ['admin', false, 403],
]);

it('returns 401 when the review queue has no token', function () {
    $this->getJson('/api/admin/ai-review-items')->assertUnauthorized();
});

it('paginates with pending items first and stable oldest-first ordering', function () {
    $olderTerminal = AiReviewItem::factory()->create([
        'status' => AiReviewStatus::REJECTED,
        'created_at' => now()->subHours(3),
    ]);
    $olderPending = AiReviewItem::factory()->create(['created_at' => now()->subHours(2)]);
    $newerPending = AiReviewItem::factory()->create(['created_at' => now()->subHour()]);
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->withToken(JWTAuth::fromUser($admin))
        ->getJson('/api/admin/ai-review-items?per_page=2')
        ->assertOk()
        ->assertJsonPath('meta.total', 3)
        ->assertJsonCount(2, 'data');

    expect($response->json('data.*.id'))->toBe([$olderPending->id, $newerPending->id])
        ->and($response->json('data'))->not->toContain($olderTerminal->id);
});

it('filters the queue by machine fields confidence date and reviewer', function () {
    $reviewer = User::factory()->create(['role' => 'admin']);
    $match = AiReviewItem::factory()->create([
        'status' => AiReviewStatus::APPROVED,
        'source' => 'cv:azure:model:v2',
        'confidence' => '0.4200',
        'reviewer_id' => $reviewer->id,
        'reviewed_at' => now(),
        'created_at' => now()->subDay(),
    ]);
    AiReviewItem::factory()->create(['source' => 'cv:openai:model:v1', 'confidence' => '0.7000']);

    $query = http_build_query([
        'status' => 'approved',
        'entity_type' => 'cv_extraction',
        'reason' => 'low_confidence',
        'source' => 'cv:azure:model:v2',
        'reviewer_id' => $reviewer->id,
        'confidence_min' => 0.4,
        'confidence_max' => 0.5,
        'created_from' => now()->subDays(2)->toDateString(),
        'created_to' => now()->toDateTimeString(),
    ]);

    $this->withToken(JWTAuth::fromUser($reviewer))
        ->getJson('/api/admin/ai-review-items?'.$query)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $match->id);
});

it('searches safe scalar fields without querying proposal JSON', function () {
    $match = AiReviewItem::factory()->create(['source' => 'cv:azure-review:model:v2']);
    AiReviewItem::factory()->create([
        'source' => 'cv:openai:model:v1',
        'proposed_value' => ['skills' => [['name' => 'azure-review']]],
    ]);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->withToken(JWTAuth::fromUser($admin))
        ->getJson('/api/admin/ai-review-items?search=azure-review')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $match->id);
});

it('returns review details without unrelated candidate private data', function () {
    $item = AiReviewItem::factory()->create();
    $candidate = $item->cvExtraction->cvDocument->candidateProfile->user;
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->withToken(JWTAuth::fromUser($admin))
        ->getJson('/api/admin/ai-review-items/'.$item->id)
        ->assertOk()
        ->assertJsonPath('data.id', $item->id)
        ->assertJsonPath('data.entity_type', 'cv_extraction');

    expect($response->getContent())->not->toContain($candidate->email)
        ->not->toContain($item->cvExtraction->raw_text);
});
