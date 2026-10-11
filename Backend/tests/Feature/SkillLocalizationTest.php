<?php

use App\Models\CandidateProfile;
use App\Models\Skill;
use App\Models\SkillAlias;
use App\Models\SkillCategory;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

function createSkillCandidate(): array
{
    $user = User::factory()->create(['role' => 'candidate']);
    $profile = CandidateProfile::factory()->create(['user_id' => $user->id]);

    return [$user, $profile];
}

function createSkillAdmin(): User
{
    return User::factory()->create(['role' => 'admin', 'is_active' => true]);
}

test('skill search validation uses the requested language with English fallback', function (string $language, string $message) {
    [$user] = createSkillCandidate();

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->getJson('/api/skills/search')
        ->assertUnprocessable()
        ->assertJsonPath('errors.q.0', $message);
})->with([
    'Arabic' => ['ar', 'حقل عبارة البحث مطلوب.'],
    'regional Arabic' => ['ar-EG', 'حقل عبارة البحث مطلوب.'],
    'English' => ['en', 'The search query field is required.'],
    'regional English' => ['en-US', 'The search query field is required.'],
    'unsupported language' => ['fr-FR', 'The search query field is required.'],
]);

test('skill search returns identical data across languages and empty results stay empty', function () {
    [$user] = createSkillCandidate();
    $category = SkillCategory::factory()->create(['name' => 'Programming Language']);
    $skill = Skill::factory()->for($category)->create(['name' => 'JavaScript', 'normalized_name' => 'javascript', 'category' => 'Language']);
    SkillAlias::factory()->for($skill)->create(['alias' => 'JS']);

    $arabic = $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', 'ar-EG')
        ->getJson('/api/skills/search?q=js')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->json('data.0');

    $english = $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', 'en-US')
        ->getJson('/api/skills/search?q=js')
        ->assertOk()
        ->json('data.0');

    expect($arabic)->toBe($english)
        ->and($arabic['id'])->toBe($skill->id)
        ->and($arabic['name'])->toBe('JavaScript')
        ->and($arabic['normalized_name'])->toBe('javascript');

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', 'ar')
        ->getJson('/api/skills/search?q=nothing-matches-this')
        ->assertOk()
        ->assertExactJson(['data' => []]);
});

test('candidate skill store rejects duplicates with a localized message', function (string $language, string $message) {
    [$user] = createSkillCandidate();
    $token = JWTAuth::fromUser($user);

    $this->withToken($token)
        ->postJson('/api/candidate/skills', ['name' => 'Laravel', 'proficiency_level' => 'Advanced'])
        ->assertCreated()
        ->assertJsonPath('data.skill.name', 'Laravel')
        ->assertJsonPath('data.skill.normalized_name', 'laravel');

    $this->withToken($token)
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/candidate/skills', ['name' => 'Laravel'])
        ->assertUnprocessable()
        ->assertJsonPath('errors.name.0', $message);
})->with([
    'Arabic' => ['ar', 'هذه المهارة مضافة بالفعل إلى ملفك الشخصي.'],
    'English' => ['en', 'This skill is already attached to your profile.'],
]);

test('candidate skill store validation uses localized attribute names', function (string $language, string $message) {
    [$user] = createSkillCandidate();

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/candidate/skills', [])
        ->assertUnprocessable()
        ->assertJsonPath('errors.name.0', $message);
})->with([
    'Arabic' => ['ar-EG', 'حقل الاسم مطلوب.'],
    'English' => ['en', 'The name field is required.'],
    'unsupported language' => ['es', 'The name field is required.'],
]);

test('missing candidate skills return localized not-found messages', function (string $language, string $message) {
    [$user] = createSkillCandidate();

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->deleteJson('/api/candidate/skills/999999')
        ->assertNotFound()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic' => ['ar', 'لم يتم العثور على مهارة المرشح.'],
    'regional Arabic' => ['ar-EG', 'لم يتم العثور على مهارة المرشح.'],
    'English' => ['en-US', 'Candidate skill not found.'],
    'unsupported language' => ['it', 'Candidate skill not found.'],
]);

test('missing taxonomy records return localized skill messages', function (string $method, string $endpoint, array $payload, string $language, string $message) {
    $admin = createSkillAdmin();
    $skill = Skill::factory()->create();

    $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', $language)
        ->{$method}(str_replace('{skill}', (string) $skill->id, $endpoint), $payload)
        ->assertNotFound()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic skill' => ['patchJson', '/api/admin/skills/999999', [], 'ar', 'لم يتم العثور على المهارة.'],
    'English skill' => ['patchJson', '/api/admin/skills/999999', [], 'en-US', 'Skill not found.'],
    'Arabic alias' => ['patchJson', '/api/admin/skills/{skill}/aliases/999999', ['alias' => 'Ghost'], 'ar-EG', 'لم يتم العثور على الاسم البديل للمهارة.'],
    'English alias' => ['patchJson', '/api/admin/skills/{skill}/aliases/999999', ['alias' => 'Ghost'], 'en', 'Skill alias not found.'],
    'fallback merge source' => ['postJson', '/api/admin/skills/999999/merge', [], 'fr', 'Skill not found.'],
]);

test('unknown skill paths return localized not-found messages', function (string $language, string $message) {
    $admin = createSkillAdmin();

    $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', $language)
        ->getJson('/api/admin/skills/1/no-such-action')
        ->assertNotFound()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic' => ['ar', 'لم يتم العثور على المهارة.'],
    'English' => ['en', 'Skill not found.'],
]);

test('skill search authentication errors use the requested language', function (string $language, string $message) {
    $this->withHeader('Accept-Language', $language)
        ->getJson('/api/skills/search?q=js')
        ->assertUnauthorized()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic' => ['ar-EG', 'غير مصادق عليه.'],
    'English' => ['en', 'Unauthenticated.'],
]);

test('non-admin roles are blocked from taxonomy management with a localized message', function (string $language, string $message) {
    $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->getJson('/api/admin/skills/1/aliases')
        ->assertForbidden()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic' => ['ar', 'فقط المسؤولون يمكنهم إدارة المهارات.'],
    'regional Arabic' => ['ar-EG', 'فقط المسؤولون يمكنهم إدارة المهارات.'],
    'English' => ['en-US', 'Only administrators can manage skills.'],
    'unsupported language' => ['de-DE', 'Only administrators can manage skills.'],
]);

test('inactive administrators are blocked with a localized message', function (string $language, string $message) {
    $user = User::factory()->create(['role' => 'admin', 'is_active' => false]);

    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/admin/skills', ['name' => 'Laravel'])
        ->assertForbidden()
        ->assertExactJson(['message' => $message]);
})->with([
    'Arabic' => ['ar', 'حسابك غير نشط.'],
    'English' => ['en', 'Your account is inactive.'],
]);

test('admin skill validation uses localized attribute names', function (string $language, string $message) {
    $admin = createSkillAdmin();

    $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/admin/skills', [])
        ->assertUnprocessable()
        ->assertJsonPath('errors.name.0', $message);
})->with([
    'Arabic' => ['ar-EG', 'حقل الاسم مطلوب.'],
    'English' => ['en', 'The name field is required.'],
]);

test('admin alias validation uses localized attribute names', function (string $language, string $message) {
    $admin = createSkillAdmin();
    $skill = Skill::factory()->create();

    $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/admin/skills/'.$skill->id.'/aliases', [])
        ->assertUnprocessable()
        ->assertJsonPath('errors.alias.0', $message);
})->with([
    'Arabic' => ['ar', 'حقل الاسم البديل مطلوب.'],
    'English' => ['en-US', 'The alias field is required.'],
]);

test('merge target validation is localized', function (string $case, string $language, string $message) {
    $admin = createSkillAdmin();
    $source = Skill::factory()->create();
    $data = match ($case) {
        'missing' => [],
        'absent' => ['target_skill_id' => 999999],
        'self' => ['target_skill_id' => $source->id],
    };

    $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', $language)
        ->postJson('/api/admin/skills/'.$source->id.'/merge', $data)
        ->assertUnprocessable()
        ->assertJsonPath('errors.target_skill_id.0', $message);
})->with([
    'Arabic missing' => ['missing', 'ar', 'حقل المهارة المستهدفة مطلوب.'],
    'English missing' => ['missing', 'en', 'The target skill field is required.'],
    'Arabic absent' => ['absent', 'ar-EG', 'قيمة حقل المهارة المستهدفة المحددة غير صالحة.'],
    'English absent' => ['absent', 'en-US', 'The selected target skill is invalid.'],
    'Arabic self merge' => ['self', 'ar', 'اختر مهارة مستهدفة مختلفة.'],
    'English self merge' => ['self', 'en', 'Choose a different target skill.'],
]);

test('duplicate taxonomy names return localized validation messages', function (string $endpoint, string $payload, string $value, string $field, string $language, string $message) {
    $admin = createSkillAdmin();
    $skill = Skill::factory()->create(['name' => 'Laravel', 'normalized_name' => 'laravel']);
    SkillAlias::factory()->for($skill)->create(['alias' => 'Laravel Framework', 'normalized_alias' => 'laravel framework']);

    $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', $language)
        ->postJson(str_replace('{skill}', (string) $skill->id, $endpoint), [$payload => $value])
        ->assertUnprocessable()
        ->assertJsonPath('errors.'.$field.'.0', $message);
})->with([
    'Arabic skill name' => ['/api/admin/skills', 'name', 'Laravel', 'name', 'ar', 'هذا الاسم مستخدم بالفعل من قبل مهارة أساسية أو اسم بديل.'],
    'English skill name' => ['/api/admin/skills', 'name', 'Laravel', 'name', 'en', 'This name is already used by a canonical skill or alias.'],
    'Arabic alias' => ['/api/admin/skills/{skill}/aliases', 'alias', 'Laravel Framework', 'alias', 'ar-EG', 'هذا الاسم مستخدم بالفعل من قبل مهارة أساسية أو اسم بديل.'],
    'English alias' => ['/api/admin/skills/{skill}/aliases', 'alias', 'Laravel Framework', 'alias', 'en-US', 'This name is already used by a canonical skill or alias.'],
]);

test('skill write and delete responses keep machine-readable values', function () {
    $admin = createSkillAdmin();

    $skillId = $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', 'ar')
        ->postJson('/api/admin/skills', ['name' => 'Laravel', 'category' => 'Language'])
        ->assertCreated()
        ->assertJsonStructure(['data' => ['id', 'name', 'normalized_name', 'category']])
        ->assertJsonPath('data.name', 'Laravel')
        ->assertJsonPath('data.normalized_name', 'laravel')
        ->json('data.id');

    $this->withToken(JWTAuth::fromUser($admin))
        ->withHeader('Accept-Language', 'en')
        ->patchJson('/api/admin/skills/'.$skillId, ['category' => 'Framework'])
        ->assertOk()
        ->assertJsonPath('data.id', $skillId)
        ->assertJsonPath('data.name', 'Laravel')
        ->assertJsonPath('data.category', 'Framework');

    [$user] = createSkillCandidate();
    $this->withToken(JWTAuth::fromUser($user))
        ->withHeader('Accept-Language', 'ar')
        ->getJson('/api/skills/search?q=lara')
        ->assertOk()
        ->assertJsonPath('data.0.id', $skillId)
        ->assertJsonPath('data.0.name', 'Laravel');
});
