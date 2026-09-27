<?php

use App\Models\Company;
use App\Models\JobPost;
use App\Models\SavedJob;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

dataset('saved job endpoints', [
    ['postJson', '/api/jobs/1/save'],
    ['deleteJson', '/api/jobs/1/save'],
    ['getJson', '/api/saved-jobs'],
]);

it('requires JWT for saved job endpoints', function (string $method, string $url) {
    $this->{$method}($url)->assertUnauthorized();
})->with('saved job endpoints');

it('allows only active candidates to use saved jobs', function (string $role, bool $active) {
    $user = User::factory()->create(['role' => $role, 'is_active' => $active]);

    $this->withToken(JWTAuth::fromUser($user))->getJson('/api/saved-jobs')->assertForbidden();
})->with([
    ['candidate', false], ['admin', true], ['super_admin', true],
]);

it('saves a job idempotently and preserves ownership', function () {
    $candidate = User::factory()->create(['role' => 'candidate']);
    $job = JobPost::factory()->create();

    $token = JWTAuth::fromUser($candidate);
    $this->withToken($token)->postJson('/api/jobs/'.$job->id.'/save')
        ->assertCreated()->assertJson(['data' => ['job_id' => $job->id, 'is_saved' => true]]);
    $this->withToken($token)->postJson('/api/jobs/'.$job->id.'/save')
        ->assertOk()->assertJson(['data' => ['job_id' => $job->id, 'is_saved' => true]]);

    expect(SavedJob::query()->where('user_id', $candidate->id)->where('job_post_id', $job->id)->count())->toBe(1);
});

it('unsaves jobs idempotently', function () {
    $candidate = User::factory()->create(['role' => 'candidate']);
    $job = JobPost::factory()->create();
    SavedJob::factory()->create(['user_id' => $candidate->id, 'job_post_id' => $job->id]);
    $token = JWTAuth::fromUser($candidate);

    $this->withToken($token)->deleteJson('/api/jobs/'.$job->id.'/save')
        ->assertOk()->assertJson(['data' => ['job_id' => $job->id, 'is_saved' => false]]);
    $this->withToken($token)->deleteJson('/api/jobs/'.$job->id.'/save')->assertOk();

    expect(SavedJob::query()->where('user_id', $candidate->id)->where('job_post_id', $job->id)->exists())->toBeFalse();
});

it('returns only the current candidates saved jobs ordered newest first', function () {
    $candidate = User::factory()->create(['role' => 'candidate']);
    $other = User::factory()->create(['role' => 'candidate']);
    $first = JobPost::factory()->create();
    $second = JobPost::factory()->create();
    $otherJob = JobPost::factory()->create();
    $oldSave = SavedJob::factory()->create(['user_id' => $candidate->id, 'job_post_id' => $first->id, 'created_at' => now()->subDay()]);
    $newSave = SavedJob::factory()->create(['user_id' => $candidate->id, 'job_post_id' => $second->id, 'created_at' => now()]);
    SavedJob::factory()->create(['user_id' => $other->id, 'job_post_id' => $otherJob->id]);

    $response = $this->withToken(JWTAuth::fromUser($candidate))->getJson('/api/saved-jobs')
        ->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('data.0.id', $second->id)
        ->assertJsonPath('data.0.is_saved', true)->assertJsonPath('data.1.id', $first->id)
        ->assertJsonPath('data.1.is_saved', true);

    expect($newSave->id)->toBeGreaterThan($oldSave->id);
});

it('keeps expired inactive and inactive-company jobs in saved jobs', function () {
    $candidate = User::factory()->create(['role' => 'candidate']);
    $company = Company::factory()->create(['is_active' => false]);
    $job = JobPost::factory()->for($company)->create(['is_active' => false, 'expires_at' => now()->subDay()]);
    SavedJob::factory()->create(['user_id' => $candidate->id, 'job_post_id' => $job->id]);

    $this->withToken(JWTAuth::fromUser($candidate))->getJson('/api/saved-jobs')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $job->id)
        ->assertJsonPath('data.0.is_saved', true)->assertJsonPath('data.0.is_expired', true)
        ->assertJsonPath('data.0.is_active', false)->assertJsonPath('data.0.company.is_active', false);
});

it('paginates saved jobs with the standard contract', function () {
    $candidate = User::factory()->create(['role' => 'candidate']);
    $jobs = JobPost::factory()->count(16)->create();
    foreach ($jobs as $job) {
        SavedJob::factory()->create(['user_id' => $candidate->id, 'job_post_id' => $job->id]);
    }
    $token = JWTAuth::fromUser($candidate);

    $this->withToken($token)->getJson('/api/saved-jobs')->assertOk()
        ->assertJsonCount(15, 'data')->assertJsonPath('meta.per_page', 15)->assertJsonPath('meta.total', 16);
    $this->withToken($token)->getJson('/api/saved-jobs?per_page=101')
        ->assertUnprocessable()->assertJsonValidationErrors(['per_page']);
});

it('exposes saved state only for the current candidate in the feed', function () {
    $candidate = User::factory()->create(['role' => 'candidate']);
    $other = User::factory()->create(['role' => 'candidate']);
    $saved = JobPost::factory()->create();
    $unsaved = JobPost::factory()->create();
    $otherSaved = JobPost::factory()->create();
    SavedJob::factory()->create(['user_id' => $candidate->id, 'job_post_id' => $saved->id]);
    SavedJob::factory()->create(['user_id' => $other->id, 'job_post_id' => $otherSaved->id]);

    $response = $this->withToken(JWTAuth::fromUser($candidate))->getJson('/api/jobs?per_page=100')->assertOk();
    $states = collect($response->json('data'))->keyBy('id');

    expect($states[$saved->id]['is_saved'])->toBeTrue()
        ->and($states[$unsaved->id]['is_saved'])->toBeFalse()
        ->and($states[$otherSaved->id]['is_saved'])->toBeFalse();
});

it('returns not found for a missing job while unsave is otherwise idempotent', function () {
    $candidate = User::factory()->create(['role' => 'candidate']);

    $this->withToken(JWTAuth::fromUser($candidate))->deleteJson('/api/jobs/999999/save')->assertNotFound();
});
