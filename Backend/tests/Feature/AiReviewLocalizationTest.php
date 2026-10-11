<?php

use App\Enums\AiReviewStatus;
use App\Models\AiReviewItem;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

it('localizes missing review items for supported regional locales and English fallback', function (string $locale, string $message) {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', $locale)
        ->getJson('/api/admin/ai-review-items/999999')
        ->assertNotFound()
        ->assertJsonPath('message', $message);
})->with([
    'English' => ['en', 'AI review item not found.'],
    'regional English' => ['en-US', 'AI review item not found.'],
    'Arabic' => ['ar', 'لم يتم العثور على عنصر مراجعة الذكاء الاصطناعي.'],
    'regional Arabic' => ['ar-EG', 'لم يتم العثور على عنصر مراجعة الذكاء الاصطناعي.'],
    'fallback' => ['fr-FR', 'AI review item not found.'],
]);

it('localizes invalid state errors without translating machine values', function (string $locale, string $message) {
    $item = AiReviewItem::factory()->create(['status' => AiReviewStatus::REJECTED]);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', $locale)
        ->postJson("/api/admin/ai-review-items/{$item->id}/approve")
        ->assertUnprocessable()
        ->assertJsonPath('errors.review_item.0', $message);

    expect($item->fresh()->status->value)->toBe('rejected')
        ->and($item->trigger_reason->value)->toBe('low_confidence');
})->with([
    'English' => ['en-US', 'This AI review item has already been decided.'],
    'Arabic' => ['ar-EG', 'تم اتخاذ قرار بشأن عنصر مراجعة الذكاء الاصطناعي هذا بالفعل.'],
]);

it('uses localized Arabic validation attributes', function () {
    $item = AiReviewItem::factory()->create();
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', 'ar-EG')
        ->postJson("/api/admin/ai-review-items/{$item->id}/reject", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('decision_reason');

    expect($response->json('errors.decision_reason.0'))->toContain('سبب القرار');
});

it('does not localize IDs confidence statuses reasons payloads timestamps or JSON keys', function () {
    $item = AiReviewItem::factory()->create();
    $admin = User::factory()->create(['role' => 'admin']);
    $token = JWTAuth::fromUser($admin);

    $english = $this->withToken($token)
        ->withHeader('Accept-Language', 'en-US')
        ->getJson("/api/admin/ai-review-items/{$item->id}")
        ->assertOk()
        ->json('data');
    $arabic = $this->withToken($token)
        ->withHeader('Accept-Language', 'ar-EG')
        ->getJson("/api/admin/ai-review-items/{$item->id}")
        ->assertOk()
        ->json('data');

    expect(array_keys($arabic))->toBe(array_keys($english));
    foreach (['id', 'entity_type', 'entity_id', 'operation', 'trigger_reason', 'confidence', 'status', 'proposed_value', 'canonical_value', 'reviewed_at', 'created_at'] as $field) {
        expect($arabic[$field])->toBe($english[$field]);
    }
});
