<?php

namespace App\Services;

use App\Exceptions\InvalidRoadmapGenerationException;
use Illuminate\Support\Facades\Validator;

class RoadmapOutputValidator
{
    /**
     * @param  array<string, mixed>  $output
     * @return array{
     *     rationale: string,
     *     phases: list<array{
     *         order: int,
     *         title: string,
     *         description?: string|null,
     *         rationale?: string|null,
     *         milestones: list<array{
     *             order: int,
     *             title: string,
     *             description?: string|null,
     *             tasks: list<array{order: int, title: string, description?: string|null, metadata?: array<string, mixed>|null}>
     *         }>
     *     }>
     * }
     */
    public function validate(array $output): array
    {
        $validator = Validator::make(['output' => $output], [
            'output' => ['required', 'array:rationale,phases'],
            'output.rationale' => ['required', 'string', 'max:5000'],
            'output.phases' => ['required', 'array', 'min:1', 'max:8'],
            'output.phases.*' => ['required', 'array:order,title,description,rationale,milestones'],
            'output.phases.*.order' => ['required', 'integer', 'min:1'],
            'output.phases.*.title' => ['required', 'string', 'max:255'],
            'output.phases.*.description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'output.phases.*.rationale' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'output.phases.*.milestones' => ['required', 'array', 'min:1', 'max:12'],
            'output.phases.*.milestones.*' => ['required', 'array:order,title,description,tasks'],
            'output.phases.*.milestones.*.order' => ['required', 'integer', 'min:1'],
            'output.phases.*.milestones.*.title' => ['required', 'string', 'max:255'],
            'output.phases.*.milestones.*.description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'output.phases.*.milestones.*.tasks' => ['required', 'array', 'min:1', 'max:20'],
            'output.phases.*.milestones.*.tasks.*' => ['required', 'array:order,title,description,metadata'],
            'output.phases.*.milestones.*.tasks.*.order' => ['required', 'integer', 'min:1'],
            'output.phases.*.milestones.*.tasks.*.title' => ['required', 'string', 'max:255'],
            'output.phases.*.milestones.*.tasks.*.description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'output.phases.*.milestones.*.tasks.*.metadata' => ['sometimes', 'nullable', 'array', 'max:20'],
        ]);

        if ($validator->fails()) {
            throw new InvalidRoadmapGenerationException('Generated roadmap failed schema validation.');
        }

        $validated = $validator->validated()['output'];
        $this->assertSequentialOrders($validated);

        /** @var array{rationale: string, phases: list<array{order: int, title: string, description?: string|null, rationale?: string|null, milestones: list<array{order: int, title: string, description?: string|null, tasks: list<array{order: int, title: string, description?: string|null, metadata?: array<string, mixed>|null}>}>}>} $validated */
        return $validated;
    }

    /** @param array<string, mixed> $validated */
    private function assertSequentialOrders(array $validated): void
    {
        foreach ($validated['phases'] as $phaseIndex => $phase) {
            if ($phase['order'] !== $phaseIndex + 1) {
                throw new InvalidRoadmapGenerationException('Generated phase ordering is invalid.');
            }

            foreach ($phase['milestones'] as $milestoneIndex => $milestone) {
                if ($milestone['order'] !== $milestoneIndex + 1) {
                    throw new InvalidRoadmapGenerationException('Generated milestone ordering is invalid.');
                }

                foreach ($milestone['tasks'] as $taskIndex => $task) {
                    if ($task['order'] !== $taskIndex + 1) {
                        throw new InvalidRoadmapGenerationException('Generated task ordering is invalid.');
                    }
                }
            }
        }
    }
}
