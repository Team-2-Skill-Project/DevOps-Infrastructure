<?php

namespace App\Services;

use App\Contracts\RoadmapGeneratorContract;
use Illuminate\Support\Str;

class TemplateRoadmapGenerator implements RoadmapGeneratorContract
{
    public function version(): string
    {
        return 'template-v1';
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function generate(array $input): array
    {
        /** @var array<string, mixed> $target */
        $target = $input['target'];
        /** @var list<array<string, mixed>> $gaps */
        $gaps = $input['priority_gaps'];
        $targetTitle = (string) $target['title'];

        return [
            'rationale' => "A task-focused roadmap for progressing toward {$targetTitle} from the candidate's current verified baseline.",
            'phases' => [
                [
                    'order' => 1,
                    'title' => 'Close priority gaps',
                    'description' => 'Address the highest-priority capability gaps first.',
                    'rationale' => 'Closing target-specific gaps creates the fastest improvement in readiness.',
                    'milestones' => $this->gapMilestones($gaps, $targetTitle),
                ],
                [
                    'order' => 2,
                    'title' => 'Build evidence',
                    'description' => 'Turn learning into demonstrable work.',
                    'milestones' => [[
                        'order' => 1,
                        'title' => 'Create target-aligned evidence',
                        'description' => 'Produce one reviewable artifact relevant to the target.',
                        'tasks' => [
                            ['order' => 1, 'title' => "Plan a {$targetTitle} portfolio artifact", 'description' => 'Define scope, acceptance criteria, and the skills the artifact will demonstrate.'],
                            ['order' => 2, 'title' => 'Complete and document the artifact', 'description' => 'Publish the result with a concise explanation of decisions and outcomes.'],
                        ],
                    ]],
                ],
                [
                    'order' => 3,
                    'title' => 'Application readiness',
                    'description' => 'Prepare evidence and messaging for the target.',
                    'milestones' => [[
                        'order' => 1,
                        'title' => 'Prepare for applications and interviews',
                        'description' => 'Connect demonstrated skills to the target requirements.',
                        'tasks' => [
                            ['order' => 1, 'title' => 'Update the CV and profile', 'description' => 'Add measurable outcomes and link the strongest evidence.'],
                            ['order' => 2, 'title' => 'Practice target-specific interview stories', 'description' => 'Prepare concise examples that demonstrate the required skills.'],
                        ],
                    ]],
                ],
            ],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $gaps
     * @return list<array<string, mixed>>
     */
    private function gapMilestones(array $gaps, string $targetTitle): array
    {
        if ($gaps === []) {
            return [[
                'order' => 1,
                'title' => 'Validate the current baseline',
                'description' => "Confirm the core expectations for {$targetTitle}.",
                'tasks' => [
                    ['order' => 1, 'title' => 'Review target expectations', 'description' => 'Compare current evidence with the target responsibilities.'],
                    ['order' => 2, 'title' => 'Choose the next growth priority', 'description' => 'Select one measurable capability to strengthen.'],
                ],
            ]];
        }

        $milestones = collect($gaps)->take(6)->values()->map(function (array $gap, int $index): array {
            $skill = Str::limit((string) ($gap['name'] ?? __('roadmap.unknown_skill')), 180, '');

            return [
                'order' => $index + 1,
                'title' => "Strengthen {$skill}",
                'description' => 'Reach the target level and produce evidence of practical use.',
                'tasks' => [
                    [
                        'order' => 1,
                        'title' => "Complete focused {$skill} practice",
                        'description' => 'Follow a bounded learning plan with a clear completion criterion.',
                        'metadata' => ['skill_id' => $gap['skill_id'] ?? null, 'gap_kind' => $gap['kind'] ?? 'missing'],
                    ],
                    [
                        'order' => 2,
                        'title' => "Demonstrate {$skill} in a practical example",
                        'description' => 'Create or extend an artifact that can be reviewed by another person.',
                        'metadata' => ['skill_id' => $gap['skill_id'] ?? null, 'gap_kind' => $gap['kind'] ?? 'missing'],
                    ],
                ],
            ];
        })->all();

        return array_values($milestones);
    }
}
