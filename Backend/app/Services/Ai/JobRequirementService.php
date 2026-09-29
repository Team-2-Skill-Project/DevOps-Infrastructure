<?php

namespace App\Services\Ai;

use App\Models\JobPost;
use App\Models\JobSkill;
use App\Models\Skill;

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
            $skill = Skill::firstOrCreate(['name' => $reqSkill['canonical_name']]);

            JobSkill::updateOrCreate(
                ['job_id' => $job->id, 'skill_id' => $skill->id],
                [
                    'type' => $reqSkill['type'], // required أو preferred
                    'confidence_score' => $reqSkill['confidence'] ?? 1.0,
                ]
            );
        }

        // تحديث معلومات الوظيفة الأساسية (Role Family, Seniority)
        $job->update([
            'role_family' => $output['role_family'] ?? null,
            'experience_level' => $output['seniority'] ?? 'entry_level',
        ]);
    }
}
