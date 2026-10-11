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
        $payload = [
            'job_id' => $job->id,
            'title' => $job->title,
            'description' => $job->description,
            'taxonomy_reference' => Skill::pluck('name')->toArray(), // مرجع المهارات للـ Normalization
        ];

        $response = $this->sendRequest('jobs/extract-requirements', $payload);
        $output = $response['output'];

        // حفظ المتطلبات وربطها بالـ Skill Taxonomy
        foreach ($output['skills'] ?? [] as $reqSkill) {
            $name = trim((string) ($reqSkill['canonical_name'] ?? $reqSkill['name'] ?? ''));

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
                    'is_required' => ($reqSkill['type'] ?? 'required') === 'required',
                    'importance' => isset($reqSkill['importance']) ? (int) $reqSkill['importance'] : null,
                    'required_level' => $reqSkill['required_level'] ?? $reqSkill['level'] ?? null,
                ]
            );
        }

        $job->update([
            'canonical_role' => $output['role_family'] ?? $output['canonical_role'] ?? null,
            'experience_level' => $output['seniority'] ?? 'entry_level',
        ]);
    }
}
