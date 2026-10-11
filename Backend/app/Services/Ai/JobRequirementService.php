<?php

namespace App\Services\Ai;

use App\Models\JobPost;
use App\Models\JobSkill;
use App\Models\Skill;
use Illuminate\Support\Str;

class JobRequirementService extends BaseAiService
{
    /**
     * إرسال تفاصيل الوظيفة للـ AI لاستخراج المتطلبات وتطبيع المهارات
     */
    public function extractAndNormalizeRequirements(JobPost $job): void
    {
        $response = $this->postJson('/api/v1/jobs/analyze', [
            'job_description' => $job->description,
        ]);
        $output = $response['profile'] ?? null;

        if (! is_array($output)) {
            $this->invalidResponse();
        }

        // حفظ المتطلبات وربطها بالـ Skill Taxonomy
        foreach ([
            ...array_map(
                static fn (mixed $skill): array => is_array($skill) ? [...$skill, 'is_required' => true] : [],
                is_array($output['required_skills'] ?? null) ? $output['required_skills'] : [],
            ),
            ...array_map(
                static fn (mixed $skill): array => is_array($skill) ? [...$skill, 'is_required' => false] : [],
                is_array($output['preferred_skills'] ?? null) ? $output['preferred_skills'] : [],
            ),
        ] as $reqSkill) {
            $name = trim((string) ($reqSkill['canonical_name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $skill = Skill::firstOrCreate(
                ['normalized_name' => Str::lower($name)],
                ['name' => $name],
            );

            JobSkill::updateOrCreate(
                ['job_post_id' => $job->id, 'skill_id' => $skill->id],
                [
                    'is_required' => (bool) ($reqSkill['is_required'] ?? false),
                    'importance' => match ($reqSkill['importance'] ?? null) {
                        'critical' => 3,
                        'important' => 2,
                        'nice_to_have' => 1,
                        default => null,
                    },
                    'required_level' => is_string($reqSkill['required_level'] ?? null)
                        ? $reqSkill['required_level']
                        : null,
                ]
            );
        }

        $job->update([
            'canonical_role' => is_string($output['canonical_role'] ?? null)
                ? $output['canonical_role']
                : (is_string($output['role_family'] ?? null) ? $output['role_family'] : null),
            'experience_level' => is_string($output['seniority'] ?? null) ? $output['seniority'] : 'entry_level',
            'min_years_experience' => is_numeric($output['min_years_experience'] ?? null)
                ? (int) $output['min_years_experience']
                : null,
            'max_years_experience' => is_numeric($output['max_years_experience'] ?? null)
                ? (int) $output['max_years_experience']
                : null,
            'responsibilities' => is_array($output['responsibilities'] ?? null)
                ? array_values(array_filter($output['responsibilities'], 'is_string'))
                : [],
        ]);
    }
}
