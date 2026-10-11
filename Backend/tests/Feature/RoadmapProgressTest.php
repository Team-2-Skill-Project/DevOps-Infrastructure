<?php

use App\Enums\RoadmapTaskStatus;
use App\Models\Roadmap;
use App\Models\RoadmapMilestone;
use App\Models\RoadmapPhase;
use App\Models\RoadmapTask;
use App\Services\RoadmapProgressService;

it('creates the ordered hierarchy with relationally valid factories and cascade deletion', function () {
    $roadmap = Roadmap::factory()->create();
    $phase = RoadmapPhase::factory()->for($roadmap)->create(['phase_order' => 1]);
    $milestone = RoadmapMilestone::factory()->for($phase, 'phase')->create(['milestone_order' => 1]);
    $task = RoadmapTask::factory()->for($roadmap)->for($milestone, 'milestone')->create([
        'step_order' => 1,
        'task_order' => 1,
    ]);

    expect($roadmap->phases()->first()->is($phase))->toBeTrue()
        ->and($phase->milestones()->first()->is($milestone))->toBeTrue()
        ->and($milestone->tasks()->first()->is($task))->toBeTrue()
        ->and($task->status)->toBe(RoadmapTaskStatus::PENDING);

    $roadmap->delete();

    $this->assertModelMissing($phase);
    $this->assertModelMissing($milestone);
    $this->assertModelMissing($task);
});

it('calculates milestone phase and roadmap progress by completed task count with rounding', function () {
    $roadmap = Roadmap::factory()->create();
    $firstPhase = RoadmapPhase::factory()->for($roadmap)->create(['phase_order' => 1]);
    $firstMilestone = RoadmapMilestone::factory()->for($firstPhase, 'phase')->create(['milestone_order' => 1]);
    $secondPhase = RoadmapPhase::factory()->for($roadmap)->create(['phase_order' => 2]);
    $secondMilestone = RoadmapMilestone::factory()->for($secondPhase, 'phase')->create(['milestone_order' => 1]);
    RoadmapTask::factory()->for($roadmap)->for($firstMilestone, 'milestone')->create([
        'step_order' => 1, 'task_order' => 1, 'status' => RoadmapTaskStatus::COMPLETED->value, 'completed_at' => now(),
    ]);
    RoadmapTask::factory()->for($roadmap)->for($firstMilestone, 'milestone')->create(['step_order' => 2, 'task_order' => 2]);
    RoadmapTask::factory()->for($roadmap)->for($secondMilestone, 'milestone')->create(['step_order' => 3, 'task_order' => 1]);

    app(RoadmapProgressService::class)->recalculate($roadmap);

    expect($firstMilestone->fresh()->progress)->toBe('50.00')
        ->and($secondMilestone->fresh()->progress)->toBe('0.00')
        ->and($firstPhase->fresh()->progress)->toBe('50.00')
        ->and($secondPhase->fresh()->progress)->toBe('0.00')
        ->and($roadmap->fresh()->overall_progress)->toBe('33.33')
        ->and($roadmap->fresh()->status)->toBe(Roadmap::STATUS_ACTIVE);
});

it('uses zero progress for empty hierarchy levels', function () {
    $roadmap = Roadmap::factory()->create(['overall_progress' => 75]);
    $phase = RoadmapPhase::factory()->for($roadmap)->create(['phase_order' => 1, 'progress' => 75]);
    $milestone = RoadmapMilestone::factory()->for($phase, 'phase')->create(['milestone_order' => 1, 'progress' => 75]);

    app(RoadmapProgressService::class)->recalculate($roadmap);

    expect($milestone->fresh()->progress)->toBe('0.00')
        ->and($milestone->fresh()->completed_at)->toBeNull()
        ->and($phase->fresh()->progress)->toBe('0.00')
        ->and($roadmap->fresh()->overall_progress)->toBe('0.00')
        ->and($roadmap->fresh()->status)->toBe(Roadmap::STATUS_ACTIVE);
});

it('marks fully completed hierarchy levels and roadmap as completed', function () {
    $roadmap = Roadmap::factory()->create();
    $phase = RoadmapPhase::factory()->for($roadmap)->create();
    $milestone = RoadmapMilestone::factory()->for($phase, 'phase')->create();
    RoadmapTask::factory()->for($roadmap)->for($milestone, 'milestone')->create([
        'status' => RoadmapTaskStatus::COMPLETED->value,
        'completed_at' => now(),
    ]);

    app(RoadmapProgressService::class)->recalculate($roadmap);

    expect($milestone->fresh()->progress)->toBe('100.00')
        ->and($milestone->fresh()->completed_at)->not->toBeNull()
        ->and($phase->fresh()->progress)->toBe('100.00')
        ->and($roadmap->fresh()->overall_progress)->toBe('100.00')
        ->and($roadmap->fresh()->status)->toBe(Roadmap::STATUS_COMPLETED);
});
