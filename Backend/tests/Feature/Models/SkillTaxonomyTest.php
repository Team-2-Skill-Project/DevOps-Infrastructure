<?php

use App\Models\CandidateSkill;
use App\Models\JobSkill;
use App\Models\Skill;
use App\Models\SkillAlias;
use App\Models\SkillCategory;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('creates readable categories with deterministic normalization and active default', function () {
    $category = SkillCategory::query()->create(['name' => ' Programming Language ', 'description' => 'Languages used in software'])->fresh();

    expect($category->name)->toBe('Programming Language');
    expect($category->normalized_name)->toBe('programming language');
    expect($category->description)->toBe('Languages used in software');
    expect($category->is_active)->toBeTrue();

    $category->update(['is_active' => false]);
    expect($category->fresh()->is_active)->toBeFalse();
});

it('rejects case and whitespace variants of an existing category', function (string $name) {
    SkillCategory::factory()->create(['name' => 'Framework']);

    expect(fn () => SkillCategory::query()->create(['name' => $name]))->toThrow(QueryException::class);
})->with(['Framework', ' framework ', 'FRAMEWORK']);

it('keeps category normalization in sync on rename', function () {
    $category = SkillCategory::factory()->create(['name' => 'Tools']);

    $category->update(['name' => ' DevOps ', 'normalized_name' => 'incorrect']);

    expect($category->fresh()->only(['name', 'normalized_name']))->toBe(['name' => 'DevOps', 'normalized_name' => 'devops']);
});

it('supports category relationships without overwriting the legacy category string', function () {
    $category = SkillCategory::factory()->create(['name' => 'Framework']);
    $skill = Skill::factory()->create(['category' => 'Backend']);
    $skill->update(['skill_category_id' => $category->id]);
    $second = Skill::factory()->for($category)->create();

    expect($skill->fresh()->skillCategory->is($category))->toBeTrue();
    expect($skill->fresh()->category)->toBe('Backend');
    expect($category->skills->modelKeys())->toEqualCanonicalizing([$skill->id, $second->id]);
});

it('allows legacy skills without a category relationship', function () {
    $skill = Skill::factory()->create(['category' => 'Backend'])->fresh();

    expect($skill->skill_category_id)->toBeNull();
    expect($skill->skillCategory)->toBeNull();
    expect($skill->category)->toBe('Backend');
});

it('supports multiple aliases for one canonical skill without changing its identity', function () {
    $skill = Skill::factory()->create(['name' => 'JavaScript', 'normalized_name' => 'javascript']);
    $attributes = $skill->refresh()->getAttributes();

    $alias = $skill->aliases()->create(['alias' => ' JS ']);
    $second = $skill->aliases()->create(['alias' => 'ECMAScript']);

    expect($alias->fresh()->only(['alias', 'normalized_alias']))->toBe(['alias' => 'JS', 'normalized_alias' => 'js']);
    expect($alias->skill->is($skill))->toBeTrue();
    expect($skill->aliases->modelKeys())->toEqualCanonicalizing([$alias->id, $second->id]);
    expect($skill->fresh()->getAttributes())->toBe($attributes);
    $this->assertDatabaseCount('skills', 1);
});

it('rejects normalized alias collisions even across canonical skills', function (string $alias) {
    SkillAlias::factory()->create(['alias' => 'JS']);
    $other = Skill::factory()->create();

    expect(fn () => $other->aliases()->create(['alias' => $alias, 'normalized_alias' => 'bypass']))
        ->toThrow(QueryException::class);
})->with(['JS', ' js ', 'js']);

it('updates alias normalization on rename without stripping punctuation', function () {
    $alias = SkillAlias::factory()->create(['alias' => 'Node']);

    $alias->update(['alias' => ' NODE.JS ', 'normalized_alias' => 'ignored']);

    expect($alias->fresh()->only(['alias', 'normalized_alias']))->toBe(['alias' => 'NODE.JS', 'normalized_alias' => 'node.js']);
});

it('preserves punctuation and internal whitespace in normalized aliases', function (string $alias, string $normalized) {
    $record = SkillAlias::factory()->create(['alias' => $alias])->fresh();

    expect($record->normalized_alias)->toBe($normalized);
})->with([['C++', 'c++'], ['C#', 'c#'], ['Visual  Basic', 'visual  basic']]);

it('deleting a canonical skill cascades only its aliases', function () {
    $skill = Skill::factory()->create();
    $aliases = SkillAlias::factory()->count(2)->for($skill)->create();
    $other = SkillAlias::factory()->create();

    $skill->delete();

    foreach ($aliases as $alias) {
        $this->assertModelMissing($alias);
    }
    $this->assertModelExists($other);
    $this->assertModelExists($other->skill);
});

it('deleting a category preserves skills legacy values and candidate and job associations', function () {
    $category = SkillCategory::factory()->create();
    $skill = Skill::factory()->for($category)->create(['category' => 'Legacy Backend']);
    $candidateSkill = CandidateSkill::factory()->for($skill)->create();
    $jobSkill = JobSkill::factory()->for($skill)->create();
    $alias = SkillAlias::factory()->for($skill)->create();

    $category->delete();

    $this->assertModelExists($skill);
    expect($skill->fresh()->skill_category_id)->toBeNull();
    expect($skill->fresh()->category)->toBe('Legacy Backend');
    expect($skill->candidateSkills->first()->is($candidateSkill))->toBeTrue();
    expect($skill->jobSkills->first()->is($jobSkill))->toBeTrue();
    expect($candidateSkill->skill->is($skill))->toBeTrue();
    expect($jobSkill->skill->is($skill))->toBeTrue();
    $this->assertModelExists($alias);
});

it('retains canonical normalized name uniqueness', function () {
    Skill::factory()->create(['name' => 'Laravel', 'normalized_name' => 'laravel']);

    expect(fn () => Skill::factory()->create(['name' => 'LARAVEL', 'normalized_name' => 'laravel']))
        ->toThrow(QueryException::class);
});

it('adds taxonomy tables without changing existing skills or associations', function () {
    $originalConnection = DB::getDefaultConnection();
    config()->set('database.connections.taxonomy_upgrade', [
        'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
    ]);
    DB::setDefaultConnection('taxonomy_upgrade');
    Schema::clearResolvedInstance('db.schema');

    try {
        Artisan::call('migrate', ['--database' => 'taxonomy_upgrade', '--force' => true]);
        $migration = require database_path('migrations/2026_09_12_224228_create_skill_taxonomy_tables.php');
        $migration->down();
        $skill = Skill::factory()->create(['name' => 'Laravel', 'normalized_name' => 'laravel', 'category' => 'Backend']);
        $attributes = $skill->refresh()->getAttributes();
        $candidateSkill = CandidateSkill::factory()->for($skill)->create();
        $jobSkill = JobSkill::factory()->for($skill)->create();

        $migration->up();

        expect($skill->fresh()->getAttributes())->toBe([...$attributes, 'skill_category_id' => null]);
        $this->assertModelExists($candidateSkill);
        $this->assertModelExists($jobSkill);
    } finally {
        DB::setDefaultConnection($originalConnection);
        Schema::clearResolvedInstance('db.schema');
        DB::purge('taxonomy_upgrade');
    }
});
