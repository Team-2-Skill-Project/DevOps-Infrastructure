<?php

use App\Models\CandidateSkill;
use App\Models\JobSkill;
use App\Models\Skill;
use App\Models\SkillAlias;
use App\Models\SkillCategory;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function () {
    config()->set('jwt.secret', str_repeat('test-secret-', 6));
});

dataset('admin taxonomy endpoints', [
    ['postJson', '/api/admin/skills'], ['patchJson', '/api/admin/skills/1'],
    ['getJson', '/api/admin/skills/1/aliases'], ['postJson', '/api/admin/skills/1/aliases'],
    ['patchJson', '/api/admin/skills/1/aliases/1'], ['deleteJson', '/api/admin/skills/1/aliases/1'],
]);

test('admin taxonomy endpoints require JWT', function (string $method, string $url) {
    $this->{$method}($url)->assertUnauthorized();
})->with('admin taxonomy endpoints');

test('admin taxonomy endpoints reject candidates and inactive administrators', function (string $method, string $url, string $role, bool $active) {
    $user = User::factory()->create(['role' => $role, 'is_active' => $active]);

    $this->withToken(JWTAuth::fromUser($user))->{$method}($url)->assertForbidden();

    $this->assertDatabaseCount('skills', 0);
    $this->assertDatabaseCount('skill_aliases', 0);
})->with('admin taxonomy endpoints')->with([['candidate', true], ['admin', false], ['super_admin', false]]);

test('search requires JWT and cannot use a web session', function () {
    $this->getJson('/api/skills/search?q=js')->assertUnauthorized();
    $this->actingAs(User::factory()->create(), 'web')->getJson('/api/skills/search?q=js')->assertUnauthorized();
});

test('all authenticated roles can search aliases and receive canonical skills once', function (string $role) {
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);
    $category = SkillCategory::factory()->create(['name' => 'Programming Language']);
    $skill = Skill::factory()->for($category)->create(['name' => 'JavaScript', 'normalized_name' => 'javascript', 'category' => 'Language']);
    SkillAlias::factory()->for($skill)->create(['alias' => 'JS']);
    SkillAlias::factory()->for($skill)->create(['alias' => 'JS language']);
    Skill::factory()->create(['name' => 'Unrelated', 'normalized_name' => 'unrelated']);

    $this->withToken(JWTAuth::fromUser($user))->getJson('/api/skills/search?q=%20JS%20')
        ->assertOk()->assertExactJson(['data' => [[
            'id' => $skill->id, 'name' => 'JavaScript', 'normalized_name' => 'javascript', 'category' => 'Language',
            'skill_category_id' => $category->id,
            'skill_category' => ['id' => $category->id, 'name' => 'Programming Language', 'normalized_name' => 'programming language', 'is_active' => true],
        ]]]);
})->with(['candidate', 'admin', 'super_admin']);

test('search prioritizes canonical then alias then prefix then contains matches', function () {
    $user = User::factory()->create();
    $contains = Skill::factory()->create(['name' => 'Some Laravel Tool', 'normalized_name' => 'some laravel tool']);
    $prefix = Skill::factory()->create(['name' => 'Laravel Nova', 'normalized_name' => 'laravel nova']);
    $aliasMatch = Skill::factory()->create(['name' => 'Framework', 'normalized_name' => 'framework']);
    SkillAlias::factory()->for($aliasMatch)->create(['alias' => 'Laravel']);
    $exact = Skill::factory()->create(['name' => 'Laravel', 'normalized_name' => 'laravel']);

    $response = $this->withToken(JWTAuth::fromUser($user))->getJson('/api/skills/search?q=LARAVEL')->assertOk();

    expect(array_column($response->json('data'), 'id'))->toBe([$exact->id, $aliasMatch->id, $prefix->id, $contains->id]);
});

test('search limits results to twenty with deterministic ordering', function () {
    $user = User::factory()->create();
    for ($index = 0; $index < 25; $index++) {
        Skill::factory()->create(['name' => 'Tool '.sprintf('%02d', $index), 'normalized_name' => 'tool '.sprintf('%02d', $index)]);
    }

    $this->withToken(JWTAuth::fromUser($user))->getJson('/api/skills/search?q=tool&limit=100')
        ->assertOk()->assertJsonCount(20, 'data')->assertJsonPath('data.0.name', 'Tool 00')->assertJsonPath('data.19.name', 'Tool 19');
});

test('search treats SQL wildcard characters as literal input', function (string $query) {
    $user = User::factory()->create();
    $skill = Skill::factory()->create(['name' => 'Tool'.$query, 'normalized_name' => 'tool'.$query]);
    Skill::factory()->create(['name' => 'Unrelated', 'normalized_name' => 'unrelated']);

    $this->withToken(JWTAuth::fromUser($user))->getJson('/api/skills/search?q='.urlencode($query))
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $skill->id);
})->with(['%', '_', '!']);

test('search validates query input', function (array $query) {
    $user = User::factory()->create();

    $this->withToken(JWTAuth::fromUser($user))->getJson('/api/skills/search?'.http_build_query($query))
        ->assertUnprocessable()->assertJsonValidationErrors(['q']);
})->with([[[]], [['q' => '   ']], [['q' => ['js']]], [['q' => str_repeat('a', 256)]]]);

test('active administrators can perform skill and alias lifecycle operations', function (string $role) {
    $admin = User::factory()->create(['role' => $role, 'is_active' => true]);
    $category = SkillCategory::factory()->create();
    $this->withToken(JWTAuth::fromUser($admin));

    $response = $this->postJson('/api/admin/skills', [
        'name' => ' JavaScript ', 'skill_category_id' => $category->id, 'category' => 'Legacy',
        'normalized_name' => 'spoofed', 'id' => 999999, 'created_at' => '2000-01-01',
    ])->assertCreated()->assertJsonPath('data.normalized_name', 'javascript')
        ->assertJsonPath('data.name', 'JavaScript')->assertJsonPath('data.skill_category.id', $category->id);
    $id = $response->json('data.id');
    $skill = Skill::findOrFail($id);
    expect($id)->not->toBe(999999);
    expect($skill->created_at->year)->not->toBe(2000);
    $candidateSkill = CandidateSkill::factory()->for($skill)->create();
    $jobSkill = JobSkill::factory()->for($skill)->create();

    $this->patchJson('/api/admin/skills/'.$id, ['name' => ' ECMAScript ', 'normalized_name' => 'ignored'])
        ->assertOk()->assertJsonPath('data.id', $id)->assertJsonPath('data.normalized_name', 'ecmascript')->assertJsonPath('data.category', 'Legacy');
    expect($candidateSkill->fresh()->skill_id)->toBe($id);
    expect($jobSkill->fresh()->skill_id)->toBe($id);

    $alias = $this->postJson('/api/admin/skills/'.$id.'/aliases', ['alias' => ' JS ', 'normalized_alias' => 'ignored', 'skill_id' => 999999])
        ->assertCreated()->assertJsonPath('data.alias', 'JS')->assertJsonPath('data.normalized_alias', 'js')->json('data.id');
    $this->getJson('/api/admin/skills/'.$id.'/aliases')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $alias);
    $this->patchJson('/api/admin/skills/'.$id.'/aliases/'.$alias, ['alias' => ' Java.Script ', 'skill_id' => 999999, 'id' => 999999])
        ->assertOk()->assertJsonPath('data.normalized_alias', 'java.script');
    $this->assertDatabaseHas('skill_aliases', ['id' => $alias, 'skill_id' => $id, 'normalized_alias' => 'java.script']);
    $this->deleteJson('/api/admin/skills/'.$id.'/aliases/'.$alias)->assertNoContent();
    $this->assertDatabaseMissing('skill_aliases', ['id' => $alias]);
    $this->assertModelExists($skill);
})->with(['admin', 'super_admin']);

test('duplicate canonical names are rejected on creation and rename', function (string $name) {
    $admin = User::factory()->create(['role' => 'admin']);
    Skill::factory()->create(['name' => 'Laravel', 'normalized_name' => 'laravel']);
    $other = Skill::factory()->create();
    $attributes = $other->refresh()->getAttributes();
    $this->withToken(JWTAuth::fromUser($admin));

    $this->postJson('/api/admin/skills', ['name' => $name])->assertUnprocessable()->assertJsonValidationErrors(['name']);
    $this->patchJson('/api/admin/skills/'.$other->id, ['name' => $name])->assertUnprocessable()->assertJsonValidationErrors(['name']);

    expect($other->fresh()->getAttributes())->toBe($attributes);
    $this->assertDatabaseCount('skills', 2);
})->with(['Laravel', ' laravel ', 'LARAVEL']);

test('canonical creation and rename reject aliases owned by another skill', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    SkillAlias::factory()->create(['alias' => 'JS']);
    $skill = Skill::factory()->create();
    $attributes = $skill->refresh()->getAttributes();
    $this->withToken(JWTAuth::fromUser($admin));

    $this->postJson('/api/admin/skills', ['name' => ' JS '])->assertUnprocessable()->assertJsonValidationErrors(['name']);
    $this->patchJson('/api/admin/skills/'.$skill->id, ['name' => 'js'])->assertUnprocessable()->assertJsonValidationErrors(['name']);

    expect($skill->fresh()->getAttributes())->toBe($attributes);
    $this->assertDatabaseCount('skills', 2);
});

test('alias creation and rename reject canonical and global alias collisions', function (bool $canonical) {
    $admin = User::factory()->create(['role' => 'admin']);
    $other = Skill::factory()->create(['name' => 'JavaScript', 'normalized_name' => 'javascript']);
    $name = $canonical ? 'JavaScript' : 'JS';
    if (! $canonical) {
        SkillAlias::factory()->for($other)->create(['alias' => $name]);
    }
    $skill = Skill::factory()->create();
    $alias = SkillAlias::factory()->for($skill)->create();
    $attributes = $alias->refresh()->getAttributes();
    $this->withToken(JWTAuth::fromUser($admin));

    $this->postJson('/api/admin/skills/'.$skill->id.'/aliases', ['alias' => ' '.$name.' '])
        ->assertUnprocessable()->assertJsonValidationErrors(['alias']);
    $this->patchJson('/api/admin/skills/'.$skill->id.'/aliases/'.$alias->id, ['alias' => $name])
        ->assertUnprocessable()->assertJsonValidationErrors(['alias']);

    expect($alias->fresh()->getAttributes())->toBe($attributes);
})->with([true, false]);

test('own canonical and alias names may coincide without ambiguity', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $skill = Skill::factory()->create(['name' => 'JavaScript', 'normalized_name' => 'javascript']);
    $alias = SkillAlias::factory()->for($skill)->create(['alias' => 'JS']);
    $this->withToken(JWTAuth::fromUser($admin));

    $this->postJson('/api/admin/skills/'.$skill->id.'/aliases', ['alias' => 'JavaScript'])->assertCreated();
    $this->patchJson('/api/admin/skills/'.$skill->id, ['name' => 'JS'])->assertOk();
    $this->patchJson('/api/admin/skills/'.$skill->id.'/aliases/'.$alias->id, ['alias' => ' js '])->assertOk();
});

test('aliases are scoped to their parent skill before validation or deletion', function (string $method) {
    $admin = User::factory()->create(['role' => 'admin']);
    $skill = Skill::factory()->create();
    $other = SkillAlias::factory()->create();

    $this->withToken(JWTAuth::fromUser($admin))->{$method}('/api/admin/skills/'.$skill->id.'/aliases/'.$other->id, ['alias' => null])
        ->assertNotFound()->assertExactJson(['message' => 'Skill alias not found.']);

    $this->assertModelExists($other);
})->with(['patchJson', 'deleteJson']);

test('category assignments can be replaced and removed without changing legacy category', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $skill = Skill::factory()->for(SkillCategory::factory())->create(['category' => 'Legacy']);
    $category = SkillCategory::factory()->create();
    $this->withToken(JWTAuth::fromUser($admin));

    $this->patchJson('/api/admin/skills/'.$skill->id, ['skill_category_id' => $category->id])
        ->assertOk()->assertJsonPath('data.skill_category.id', $category->id);
    $this->patchJson('/api/admin/skills/'.$skill->id, ['skill_category_id' => null])
        ->assertOk()->assertJsonPath('data.skill_category', null)->assertJsonPath('data.category', 'Legacy');

    expect($skill->fresh()->skill_category_id)->toBeNull();
});

test('skill input validation rejects invalid data', function (array $data, string $field) {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/skills', $data)
        ->assertUnprocessable()->assertJsonValidationErrors([$field]);

    $this->assertDatabaseCount('skills', 0);
})->with([
    [[], 'name'], [['name' => ' '], 'name'], [['name' => []], 'name'], [['name' => str_repeat('a', 256)], 'name'],
    [['name' => 'Laravel', 'skill_category_id' => 999999], 'skill_category_id'],
    [['name' => 'Laravel', 'category' => []], 'category'],
]);

test('alias input validation rejects invalid data', function (array $data) {
    $admin = User::factory()->create(['role' => 'admin']);
    $skill = Skill::factory()->create();

    $this->withToken(JWTAuth::fromUser($admin))->postJson('/api/admin/skills/'.$skill->id.'/aliases', $data)
        ->assertUnprocessable()->assertJsonValidationErrors(['alias']);

    $this->assertDatabaseCount('skill_aliases', 0);
})->with([[[]], [['alias' => ' ']], [['alias' => []]], [['alias' => str_repeat('a', 256)]]]);
