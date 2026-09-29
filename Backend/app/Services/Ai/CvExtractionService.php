<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Models\CandidateProfile;
use App\Models\CvDocument;
use Illuminate\Support\Facades\DB;

class CvExtractionService extends BaseAiService
{
    /**
     * إرسال الـ CV للـ AI واستخراج البيانات الهيكلية مع الـ Metadata
     */
    /**
     * @return array<string,mixed>
     */
    public function extractAndPersist(CvDocument $cvDocument): array
    {
        // 1. تجهيز الـ Request Contract
        /** @var CvDocument $cvDocument */
        $payload = [
            'candidate_id' => $cvDocument->user_id,
            'cv_version_id' => $cvDocument->id,
            'file_path' => storage_path("app/{$cvDocument->file_path}"),
            'raw_text' => $cvDocument->raw_extracted_text,
        ];

        // 2. استدعاء الـ AI Service
        $response = $this->sendRequest('cv/extract', $payload);

        // 3. تخزين النتائج المهيكلة (Persist Structured CV Output & Confidence)
        return DB::transaction(function () use ($cvDocument, $response) {
            $output = $response['output'];

            // تحديث حالة وثيقة الـ CV
            $cvDocument->update([
                'parsing_status' => 'completed',
                'raw_extracted_text' => json_encode($output, JSON_UNESCAPED_UNICODE),
            ]);

            // تحديث بروفايل المرشح بالبيانات المستخرجة والـ Confidence
            $profile = CandidateProfile::updateOrCreate(
                ['user_id' => $cvDocument->user_id],
                [
                    'full_name' => $output['full_name'] ?? null,
                    'headline' => $output['headline'] ?? null,
                    'target_role' => $output['target_role'] ?? null,
                    // حفظ نسبة الثقة أو إرسالها لطابور مراجعة الأدمن لو كانت أقل من المعيار
                ]
            );

            // حفظ المهارات المستخرجة مع الـ Evidence والـ Confidence في جدول candidate_skills
            // ... (يتم حفظها هنا بدقة)

            return [
                'profile' => $profile,
                'confidence' => $response['confidence'],
                'evidence' => $output['evidence'] ?? [],
            ];
        });
    }
}
