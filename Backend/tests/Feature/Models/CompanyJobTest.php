<?php

use App\Models\Company;
use App\Models\CompanyAlias;
use App\Models\JobPost;
use App\Models\JobSkill;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\QueryException;

it('links companies aliases jobs creators and skills correctly', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $company = Company::factory()->create([
        'created_by' => $admin->id,
    ]);

    $alias = CompanyAlias::factory()->create([
        'company_id' => $company->id,
    ]);

    $job = JobPost::factory()->create([
        'company_id' => $company->id,
        'created_by' => $admin->id,
    ]);

    $skill = Skill::factory()->create();

    $jobSkill = JobSkill::factory()->create([
        'job_post_id' => $job->id,
        'skill_id' => $skill->id,
    ]);

    expect($company->creator->is($admin))->toBeTrue()
        ->and($company->aliases->first()->is($alias))->toBeTrue()
        ->and($company->jobPosts->first()->is($job))->toBeTrue()
        ->and($job->company->is($company))->toBeTrue()
        ->and($job->creator->is($admin))->toBeTrue()
        ->and($job->jobSkills->first()->is($jobSkill))->toBeTrue()
        ->and($jobSkill->skill->is($skill))->toBeTrue()
        ->and($skill->jobSkills->first()->is($jobSkill))->toBeTrue()
        ->and($admin->companiesCreated->first()->is($company))->toBeTrue()
        ->and($admin->jobPostsCreated->first()->is($job))->toBeTrue();
});

it('rejects duplicate normalized company names', function () {
    Company::factory()->create([
        'normalized_name' => 'google',
    ]);

    expect(fn () => Company::factory()->create([
        'normalized_name' => 'google',
    ]))->toThrow(QueryException::class);
});

it('rejects duplicate normalized company aliases', function () {
    $company = Company::factory()->create();

    CompanyAlias::factory()->create([
        'company_id' => $company->id,
        'normalized_alias' => 'google llc',
    ]);

    expect(fn () => CompanyAlias::factory()->create([
        'company_id' => $company->id,
        'normalized_alias' => 'google llc',
    ]))->toThrow(QueryException::class);
});

it('rejects duplicate skills for the same job', function () {
    $job = JobPost::factory()->create();
    $skill = Skill::factory()->create();

    JobSkill::factory()->create([
        'job_post_id' => $job->id,
        'skill_id' => $skill->id,
    ]);

    expect(fn () => JobSkill::factory()->create([
        'job_post_id' => $job->id,
        'skill_id' => $skill->id,
    ]))->toThrow(QueryException::class);
});

it('deleting a company removes aliases jobs and their job skills', function () {
    $company = Company::factory()->create();

    $alias = CompanyAlias::factory()->create([
        'company_id' => $company->id,
    ]);

    $job = JobPost::factory()->create([
        'company_id' => $company->id,
    ]);

    $skill = Skill::factory()->create();

    $jobSkill = JobSkill::factory()->create([
        'job_post_id' => $job->id,
        'skill_id' => $skill->id,
    ]);

    $company->delete();

    expect(CompanyAlias::find($alias->id))->toBeNull()
        ->and(JobPost::find($job->id))->toBeNull()
        ->and(JobSkill::find($jobSkill->id))->toBeNull()
        ->and(Skill::find($skill->id))->not->toBeNull();
});

it('deleting a job removes its job skills without deleting the taxonomy skill', function () {
    $job = JobPost::factory()->create();
    $skill = Skill::factory()->create();

    $jobSkill = JobSkill::factory()->create([
        'job_post_id' => $job->id,
        'skill_id' => $skill->id,
    ]);

    $job->delete();

    expect(JobSkill::find($jobSkill->id))->toBeNull()
        ->and(Skill::find($skill->id))->not->toBeNull();
});

it('deleting a skill removes job skill associations without deleting the job', function () {
    $job = JobPost::factory()->create();
    $skill = Skill::factory()->create();

    $jobSkill = JobSkill::factory()->create([
        'job_post_id' => $job->id,
        'skill_id' => $skill->id,
    ]);

    $skill->delete();

    expect(JobSkill::find($jobSkill->id))->toBeNull()
        ->and(JobPost::find($job->id))->not->toBeNull();
});
