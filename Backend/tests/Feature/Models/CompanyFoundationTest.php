<?php

use App\Models\Company;
use App\Models\CompanyAlias;
use App\Models\JobPost;

it('generates a normalized company name when omitted', function () {
    $company = Company::factory()->create([
        'name' => '  Example, Inc.  ',
        'normalized_name' => null,
    ]);

    expect($company->normalized_name)->toBe('example, inc.')
        ->and($company->name)->toBe('  Example, Inc.  ');
});

it('preserves an explicitly supplied normalized company name', function () {
    $company = Company::factory()->create([
        'name' => 'Example, Inc.',
        'normalized_name' => 'custom-company-key',
    ]);

    expect($company->normalized_name)->toBe('custom-company-key');
});

it('generates a normalized alias when omitted', function () {
    $alias = CompanyAlias::factory()->create([
        'alias' => '  Example LLC  ',
        'normalized_alias' => null,
    ]);

    expect($alias->normalized_alias)->toBe('example llc')
        ->and($alias->alias)->toBe('  Example LLC  ');
});

it('preserves an explicitly supplied normalized alias', function () {
    $alias = CompanyAlias::factory()->create([
        'alias' => 'Example LLC',
        'normalized_alias' => 'custom-alias-key',
    ]);

    expect($alias->normalized_alias)->toBe('custom-alias-key');
});

it('casts company status fields to booleans', function () {
    $company = Company::factory()->create([
        'is_verified' => 1,
        'is_active' => 0,
    ]);

    expect($company->is_verified)->toBeTrue()
        ->and($company->is_active)->toBeFalse();
});

it('connects companies to their aliases and job posts', function () {
    $company = Company::factory()->create();
    $alias = CompanyAlias::factory()->for($company)->create();
    $job = JobPost::factory()->for($company)->create();

    expect($company->aliases->first()->is($alias))->toBeTrue()
        ->and($company->jobPosts->first()->is($job))->toBeTrue();
});

it('connects a company alias to its company', function () {
    $company = Company::factory()->create();
    $alias = CompanyAlias::factory()->for($company)->create();

    expect($alias->company->is($company))->toBeTrue();
});

it('keeps job posts attached when a company is deactivated', function () {
    $company = Company::factory()->create();
    $job = JobPost::factory()->for($company)->create();

    $company->update(['is_active' => false]);

    expect($company->fresh()->is_active)->toBeFalse()
        ->and(JobPost::find($job->id))->not->toBeNull()
        ->and(JobPost::find($job->id)->company_id)->toBe($company->id);
});
