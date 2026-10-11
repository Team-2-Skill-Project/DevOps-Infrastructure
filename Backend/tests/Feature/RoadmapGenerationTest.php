<?php

use App\Contracts\RoadmapGeneratorContract;
use App\Exceptions\InvalidRoadmapGenerationException;
use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\JobMatch;
use App\Models\JobPost;
use App\Models\JobSkill;
use App\Models\Skill;
use App\Services\RoadmapGenerationInputBuilder;
use App\Services\RoadmapGenerationService;
use App\Services\RoadmapOutputValidator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

function validRoadmapGenerationOutput(): array
{
    return [
        'rationale' => 'Close the most important gaps and build evidence.',
        'phases' => [[
            'order' => 1,
            'title' => 'Foundations',
            'description' => 'Build the required foundation.',
            'rationale' => 'The foundation supports later work.',
            'milestones' => [[
                'order' => 1,
                'title' => 'Complete the foundation',
                'description' => 'Finish a measurable unit of work.',
                'tasks' => [[
                    'order' => 1,
                    'title' => 'Complete focused practice',
                    'description' => 'Finish the exercise and record evidence.',
                    'metadata' => ['week' => 1],
                ]],
            ]],
        ]],
    ];
}

function bindRoadmapGenerator(array $output, string $version = 'fake-v1'): void
{
    app()->instance(RoadmapGeneratorContract::class, new class($output, $version) implements RoadmapGeneratorContract
    {
        public function __construct(private array $output, private string $generatorVersion) {}

        public function generate(array $input): array
        {
            return $this->output;
        }

        public function version(): string
        {
            return $this->generatorVersion;
        }
    });
}

it('builds generation input from the server-owned baseline gaps and job target', function () {
    $profile = CandidateProfile::factory()->create(['job_title' => 'PHP Developer']);
    $laravel = Skill::factory()->create(['name' => 'Laravel']);
    $docker = Skill::factory()->create(['name' => 'Docker']);
    CandidateSkill::factory()->for($profile)->for($laravel)->create(['source' => CandidateSkill::SOURCE_MANUAL]);
    $job = JobPost::factory()->create(['title' => 'Backend Engineer', 'canonical_role' => 'Platform Engineer']);
    JobSkill::factory()->for($job)->for($docker)->create(['is_required' => true, 'importance' => 5]);
    JobMatch::factory()->for($profile)->for($job)->create([
        'missing_skills' => [['skill_id' => $docker->id, 'importance' => 'critical']],
        'weak_skills' => [],
    ]);

    $input = app(RoadmapGenerationInputBuilder::class)->build($profile, 'job', targetJobId: $job->id);

    expect($input['baseline']['profile']['job_title'])->toBe('PHP Developer')
        ->and($input['baseline']['skills'][0]['name'])->toBe('Laravel')
        ->and($input['priority_gaps'][0])->toMatchArray(['skill_id' => $docker->id, 'name' => 'Docker', 'kind' => 'missing'])
        ->and($input['target'])->toMatchArray(['type' => 'job', 'id' => $job->id, 'title' => 'Backend Engineer']);
});

it('stores validated generated hierarchy atomically with audit fields', function () {
    bindRoadmapGenerator(validRoadmapGenerationOutput());
    $profile = CandidateProfile::factory()->create();
    $this->freezeTime();

    $roadmap = app(RoadmapGenerationService::class)->generate($profile, 'role', 'Backend Engineer');

    expect($roadmap->candidate_profile_id)->toBe($profile->id)
        ->and($roadmap->target_role)->toBe('Backend Engineer')
        ->and($roadmap->generation_version)->toBe('fake-v1')
        ->and($roadmap->generated_at->toDateTimeString())->toBe(now()->toDateTimeString())
        ->and($roadmap->generation_input_snapshot)->toHaveKeys(['baseline', 'priority_gaps', 'target'])
        ->and($roadmap->phases)->toHaveCount(1)
        ->and($roadmap->phases->first()->milestones)->toHaveCount(1)
        ->and($roadmap->phases->first()->milestones->first()->tasks)->toHaveCount(1);

    $this->assertDatabaseHas('roadmap_steps', [
        'roadmap_id' => $roadmap->id,
        'task_order' => 1,
        'title' => 'Complete focused practice',
    ]);
});

it('rejects malformed generator output before creating any roadmap records', function () {
    bindRoadmapGenerator(['rationale' => 'Invalid', 'phases' => []]);
    $profile = CandidateProfile::factory()->create();

    expect(fn () => app(RoadmapGenerationService::class)->generate($profile, 'role', 'Backend Engineer'))
        ->toThrow(InvalidRoadmapGenerationException::class);

    $this->assertDatabaseCount('roadmaps', 0);
    $this->assertDatabaseCount('roadmap_phases', 0);
    $this->assertDatabaseCount('roadmap_milestones', 0);
    $this->assertDatabaseCount('roadmap_steps', 0);
});

it('rejects nonsequential generated ordering', function () {
    $output = validRoadmapGenerationOutput();
    $output['phases'][0]['milestones'][0]['tasks'][0]['order'] = 2;

    expect(fn () => app(RoadmapOutputValidator::class)->validate($output))
        ->toThrow(InvalidRoadmapGenerationException::class);
});

it('rolls back the complete hierarchy when a child insert fails', function () {
    bindRoadmapGenerator(validRoadmapGenerationOutput());
    $profile = CandidateProfile::factory()->create();
    DB::unprepared("CREATE TRIGGER fail_roadmap_task_insert BEFORE INSERT ON roadmap_steps BEGIN SELECT RAISE(ABORT, 'task insert failed'); END");

    expect(fn () => app(RoadmapGenerationService::class)->generate($profile, 'role', 'Backend Engineer'))
        ->toThrow(QueryException::class);

    $this->assertDatabaseCount('roadmaps', 0);
    $this->assertDatabaseCount('roadmap_phases', 0);
    $this->assertDatabaseCount('roadmap_milestones', 0);
    $this->assertDatabaseCount('roadmap_steps', 0);
});

it('rejects unexpected generator keys', function () {
    $output = validRoadmapGenerationOutput();
    $output['unexpected'] = true;

    expect(fn () => app(RoadmapOutputValidator::class)->validate($output))
        ->toThrow(InvalidRoadmapGenerationException::class);
});
