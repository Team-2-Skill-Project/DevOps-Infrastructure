<?php

use App\Contracts\RoadmapGeneratorContract;
use App\Models\CandidateProfile;
use App\Services\RoadmapGenerationService;
use Tymon\JWTAuth\Facades\JWTAuth;

it('localizes roadmap generation messages for supported regional and fallback locales', function (string $locale, string $message) {
    $profile = CandidateProfile::factory()->create();

    $this->withHeader('Accept-Language', $locale)
        ->withToken(JWTAuth::fromUser($profile->user))
        ->postJson('/api/roadmaps', ['target_type' => 'role', 'target_role' => 'Backend Engineer'])
        ->assertCreated()
        ->assertJsonPath('message', $message);
})->with([
    'Arabic' => ['ar', 'تم إنشاء خطة التطوير بنجاح.'],
    'Egyptian Arabic' => ['ar-EG', 'تم إنشاء خطة التطوير بنجاح.'],
    'English' => ['en', 'Roadmap generated successfully.'],
    'US English' => ['en-US', 'Roadmap generated successfully.'],
    'unsupported fallback' => ['fr-FR', 'Roadmap generated successfully.'],
]);

it('localizes roadmap ownership failures without exposing another candidates record', function (string $locale, string $message) {
    $owner = CandidateProfile::factory()->create();
    $other = CandidateProfile::factory()->create();
    $roadmap = app(RoadmapGenerationService::class)->generate($owner, 'role', 'Backend Engineer');

    $this->withHeader('Accept-Language', $locale)
        ->withToken(JWTAuth::fromUser($other->user))
        ->getJson("/api/roadmaps/{$roadmap->id}")
        ->assertNotFound()
        ->assertJsonPath('message', $message);
})->with([
    'Arabic' => ['ar', 'لم يتم العثور على خطة التطوير.'],
    'Egyptian Arabic' => ['ar-EG', 'لم يتم العثور على خطة التطوير.'],
    'English' => ['en', 'Roadmap not found.'],
    'US English' => ['en-US', 'Roadmap not found.'],
    'unsupported fallback' => ['de-DE', 'Roadmap not found.'],
]);

it('uses localized validation attributes for generation requests', function (string $locale, string $attribute) {
    $profile = CandidateProfile::factory()->create();

    $response = $this->withHeader('Accept-Language', $locale)
        ->withToken(JWTAuth::fromUser($profile->user))
        ->postJson('/api/roadmaps', ['target_type' => 'role'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['target_role']);

    expect($response->json('errors.target_role.0'))->toContain($attribute);
})->with([
    'Arabic' => ['ar', 'الدور المستهدف'],
    'Egyptian Arabic' => ['ar-EG', 'الدور المستهدف'],
    'English' => ['en', 'target role'],
    'US English' => ['en-US', 'target role'],
    'unsupported fallback' => ['fr', 'target role'],
]);

it('localizes malformed generator responses and leaves no partial roadmap', function (string $locale, string $message) {
    app()->instance(RoadmapGeneratorContract::class, new class implements RoadmapGeneratorContract
    {
        public function generate(array $input): array
        {
            return ['rationale' => 'Invalid', 'phases' => []];
        }

        public function version(): string
        {
            return 'invalid-v1';
        }
    });
    $profile = CandidateProfile::factory()->create();

    $this->withHeader('Accept-Language', $locale)
        ->withToken(JWTAuth::fromUser($profile->user))
        ->postJson('/api/roadmaps', ['target_type' => 'role', 'target_role' => 'Backend Engineer'])
        ->assertStatus(502)
        ->assertJsonPath('message', $message);

    $this->assertDatabaseCount('roadmaps', 0);
})->with([
    'Arabic' => ['ar-EG', 'تعذر التحقق من خطة التطوير المُنشأة. يرجى المحاولة مرة أخرى.'],
    'English fallback' => ['fr', 'The generated roadmap could not be validated. Please try again.'],
]);
