<?php

use App\Models\JobPost;
use App\Models\JobSource;
use App\Models\User;
use Database\Seeders\JobSourceSeeder;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Queue;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

function jobSourceAdminToken(string $role = 'admin', bool $active = true): string
{
    return JWTAuth::fromUser(User::factory()->create(['role' => $role, 'is_active' => $active]));
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function jobSourcePayload(array $overrides = []): array
{
    return array_replace([
        'name' => 'Example Jobs', 'slug' => 'example-jobs', 'base_url' => 'https://example.com',
        'source_type' => 'public', 'collection_method' => 'api',
    ], $overrides);
}

it('requires authentication on every source endpoint', function (string $method, string $path) {
    $this->{$method}('/api/admin/job-sources'.$path)->assertUnauthorized();
})->with([['getJson', ''], ['postJson', ''], ['getJson', '/1'], ['patchJson', '/1']]);

it('forbids candidates and inactive administrators on every source endpoint', function (string $role, bool $active) {
    $source = JobSource::factory()->create();
    $this->withToken(jobSourceAdminToken($role, $active));

    $this->getJson('/api/admin/job-sources')->assertForbidden();
    $this->postJson('/api/admin/job-sources', jobSourcePayload())->assertForbidden();
    $this->getJson("/api/admin/job-sources/{$source->id}")->assertForbidden();
    $this->patchJson("/api/admin/job-sources/{$source->id}", ['is_active' => false])->assertForbidden();

    expect($source->fresh()->is_active)->toBeTrue();
    $this->assertDatabaseCount('job_sources', 1);
})->with([['candidate', true], ['admin', false], ['super_admin', false]]);

it('allows active administrators to create sources with defaults and creator attribution', function (string $role) {
    $admin = User::factory()->create(['role' => $role]);

    $response = $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/job-sources', jobSourcePayload(['name' => ' Example Jobs ', 'slug' => ' example-jobs ']))
        ->assertCreated()->assertJsonPath('data.name', 'Example Jobs')
        ->assertJsonPath('data.slug', 'example-jobs')->assertJsonPath('data.created_by', $admin->id)
        ->assertJsonPath('data.is_active', true)->assertJsonPath('data.schedule_enabled', false)
        ->assertJsonPath('data.schedule_expression', null);

    $this->assertDatabaseHas('job_sources', ['id' => $response->json('data.id'), 'created_by' => $admin->id, 'slug' => 'example-jobs']);
})->with(['admin', 'super_admin']);

it('accepts supported machine values', function (string $type, string $method) {
    $this->withToken(jobSourceAdminToken())->postJson('/api/admin/job-sources', jobSourcePayload([
        'source_type' => $type, 'collection_method' => $method,
        'base_url' => in_array($method, ['manual', 'file'], true) ? null : 'https://example.com',
    ]))->assertCreated()->assertJsonPath('data.source_type', $type)->assertJsonPath('data.collection_method', $method);
})->with([['public', 'scraper'], ['authorized', 'api'], ['manual', 'manual'], ['authorized', 'file']]);

it('rejects invalid fields without creating a source', function (array $overrides, string $field) {
    $this->withToken(jobSourceAdminToken())->postJson('/api/admin/job-sources', jobSourcePayload($overrides))
        ->assertUnprocessable()->assertJsonValidationErrors($field);

    $this->assertDatabaseCount('job_sources', 0);
})->with([
    [['source_type' => 'private'], 'source_type'],
    [['collection_method' => 'App\\Collectors\\Custom'], 'collection_method'],
    [['base_url' => 'not-a-url'], 'base_url'],
    [['base_url' => 'file:///etc/passwd'], 'base_url'],
    [['base_url' => null], 'base_url'],
    [['slug' => 'bad slug'], 'slug'],
    [['schedule_enabled' => true], 'schedule_expression'],
    [['schedule_expression' => '* * * * *; command'], 'schedule_expression'],
    [['schedule_enabled' => true, 'schedule_expression' => '61 * * * *'], 'schedule_expression'],
    [['collection_method' => 'manual', 'schedule_enabled' => true, 'schedule_expression' => '* * * * *'], 'schedule_enabled'],
    [['collection_method' => 'file', 'schedule_enabled' => true, 'schedule_expression' => '* * * * *'], 'schedule_enabled'],
    [['created_by' => 123], 'created_by'],
]);

it('enforces slug uniqueness for creation and update but accepts an unchanged slug', function () {
    $source = JobSource::factory()->create(['slug' => 'example-jobs']);
    $other = JobSource::factory()->create();
    $this->withToken(jobSourceAdminToken());

    $this->postJson('/api/admin/job-sources', jobSourcePayload())->assertUnprocessable()->assertJsonValidationErrors('slug');
    $this->patchJson("/api/admin/job-sources/{$other->id}", ['slug' => $source->slug])->assertUnprocessable()->assertJsonValidationErrors('slug');
    $this->patchJson("/api/admin/job-sources/{$source->id}", ['slug' => $source->slug])->assertOk();

    $this->assertDatabaseCount('job_sources', 2);
});

it('preserves omitted fields and protects creator attribution on patch', function () {
    $creator = User::factory()->create(['role' => 'admin']);
    $source = JobSource::factory()->create(['created_by' => $creator->id, 'notes' => 'Keep', 'schedule_enabled' => true, 'schedule_expression' => '0 * * * *']);
    $this->withToken(jobSourceAdminToken());

    $this->patchJson("/api/admin/job-sources/{$source->id}", ['name' => 'Updated'])
        ->assertOk()->assertJsonPath('data.name', 'Updated')->assertJsonPath('data.notes', 'Keep')
        ->assertJsonPath('data.slug', $source->slug)->assertJsonPath('data.base_url', $source->base_url)
        ->assertJsonPath('data.created_by', $creator->id)->assertJsonPath('data.schedule_expression', '0 * * * *');
    $this->patchJson("/api/admin/job-sources/{$source->id}", ['created_by' => 999])->assertUnprocessable()->assertJsonValidationErrors('created_by');

    expect($source->fresh()->created_by)->toBe($creator->id);
});

it('stores activation and schedule independently without changing existing jobs or dispatching jobs', function () {
    Queue::fake();
    $source = JobSource::factory()->create(['slug' => 'legacy-source']);
    $job = JobPost::factory()->create(['source' => 'legacy-source', 'external_id' => 'legacy-1', 'external_url' => 'https://example.com/jobs/1']);
    $originalJob = $job->fresh()->getAttributes();
    $this->withToken(jobSourceAdminToken());

    $this->patchJson("/api/admin/job-sources/{$source->id}", ['schedule_enabled' => true, 'schedule_expression' => '*/15 * * * *'])
        ->assertOk()->assertJsonPath('data.schedule_enabled', true);
    $this->patchJson("/api/admin/job-sources/{$source->id}", ['is_active' => false])
        ->assertOk()->assertJsonPath('data.is_active', false)->assertJsonPath('data.schedule_enabled', true);
    $this->patchJson("/api/admin/job-sources/{$source->id}", ['is_active' => true, 'schedule_enabled' => false])
        ->assertOk()->assertJsonPath('data.is_active', true)->assertJsonPath('data.schedule_expression', '*/15 * * * *');

    $this->assertModelExists($source);
    expect($job->fresh()->getAttributes())->toBe($originalJob);
    Queue::assertNothingPushed();
});

it('validates merged schedule and method configuration on partial patches', function () {
    $source = JobSource::factory()->create(['schedule_enabled' => true, 'schedule_expression' => '0 * * * *']);
    $this->withToken(jobSourceAdminToken());

    $this->patchJson("/api/admin/job-sources/{$source->id}", ['collection_method' => 'manual'])->assertUnprocessable()->assertJsonValidationErrors('schedule_enabled');
    $this->patchJson("/api/admin/job-sources/{$source->id}", ['base_url' => null])->assertUnprocessable()->assertJsonValidationErrors('base_url');
    $this->patchJson("/api/admin/job-sources/{$source->id}", ['schedule_expression' => null])->assertUnprocessable()->assertJsonValidationErrors('schedule_expression');
    $this->patchJson("/api/admin/job-sources/{$source->id}", ['collection_method' => 'manual', 'schedule_enabled' => false, 'base_url' => null])->assertOk();
    $this->patchJson("/api/admin/job-sources/{$source->id}", ['collection_method' => 'api'])->assertUnprocessable()->assertJsonValidationErrors('base_url');

    expect($source->fresh()->collection_method)->toBe('manual');
});

it('lists paginated sources with configuration filters and name or slug search', function () {
    $match = JobSource::factory()->create(['name' => 'Distinct Source', 'slug' => 'distinct-source', 'source_type' => 'authorized', 'collection_method' => 'scraper', 'is_active' => false, 'schedule_enabled' => true, 'schedule_expression' => '0 * * * *']);
    JobSource::factory()->count(2)->sequence(
        ['name' => 'Unrelated One', 'slug' => 'unrelated-one'],
        ['name' => 'Unrelated Two', 'slug' => 'unrelated-two'],
    )->create();
    $this->withToken(jobSourceAdminToken());

    $this->getJson('/api/admin/job-sources?per_page=1')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('meta.total', 3);
    foreach (['is_active=false', 'source_type=authorized', 'collection_method=scraper', 'schedule_enabled=true', 'search=Distinct', 'search=distinct-source'] as $query) {
        $this->getJson('/api/admin/job-sources?'.$query)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $match->id);
    }
    $this->getJson('/api/admin/job-sources?per_page=101')->assertUnprocessable()->assertJsonValidationErrors('per_page');
    $this->getJson("/api/admin/job-sources/{$match->id}")->assertOk()
        ->assertJsonPath('data.schedule_expression', '0 * * * *')->assertJsonPath('data.base_url', $match->base_url);
    $this->getJson('/api/admin/job-sources/999999')->assertNotFound();
});

it('localizes feature errors without translating machine values', function (string $locale, string $message) {
    $this->withHeader('Accept-Language', $locale);
    $this->withToken(jobSourceAdminToken());

    $this->postJson('/api/admin/job-sources', jobSourcePayload(['schedule_enabled' => true]))
        ->assertUnprocessable()->assertJsonPath('errors.schedule_expression.0', $message);
    $this->postJson('/api/admin/job-sources', jobSourcePayload(['schedule_enabled' => true, 'schedule_expression' => '0 * * * *']))
        ->assertCreated()->assertJsonPath('data.source_type', 'public')->assertJsonPath('data.collection_method', 'api')->assertJsonPath('data.slug', 'example-jobs');

    foreach (['base_url_required', 'automatic_method_required', 'schedule_required', 'invalid_schedule', 'slug_taken'] as $key) {
        expect(Lang::hasForLocale('job_sources.'.$key, $locale))->toBeTrue();
    }
})->with([
    ['en', 'A cron expression is required when scheduling is enabled.'],
    ['ar', 'تعبير cron مطلوب عند تفعيل الجدولة.'],
]);

it('retains a source when its creator is deleted', function () {
    $creator = User::factory()->create();
    $source = JobSource::factory()->create(['created_by' => $creator->id]);

    $creator->delete();

    expect($source->fresh()->created_by)->toBeNull();
});

it('seeds a manual source without overwriting existing configuration', function () {
    $this->seed(JobSourceSeeder::class);
    $source = JobSource::query()->where('slug', 'manual')->sole();
    $source->update(['name' => 'Configured source', 'is_active' => false]);

    $this->seed(JobSourceSeeder::class);

    $this->assertDatabaseCount('job_sources', 1);
    expect($source->fresh())->toMatchArray(['name' => 'Configured source', 'is_active' => false, 'schedule_enabled' => false, 'base_url' => null]);
});

it('does not accept executable configuration fields or invalid machine values on patch', function () {
    $source = JobSource::factory()->create();
    $this->withToken(jobSourceAdminToken());

    $this->patchJson("/api/admin/job-sources/{$source->id}", ['source_type' => 'unknown', 'collection_method' => 'custom'])
        ->assertUnprocessable()->assertJsonValidationErrors(['source_type', 'collection_method']);
    $this->patchJson("/api/admin/job-sources/{$source->id}", ['collector_class' => 'App\\Untrusted', 'command' => 'run-something', 'name' => 'Safe'])
        ->assertOk()->assertJsonMissingPath('data.collector_class')->assertJsonMissingPath('data.command');

    expect($source->fresh()->name)->toBe('Safe');
    expect($source->fresh()->source_type)->toBe('public');
    expect($source->fresh()->collection_method)->toBe('api');
});
