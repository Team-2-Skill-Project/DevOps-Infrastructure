<?php

namespace App\Services;

use App\Enums\ExtractionSource;
use App\Models\CandidateSkill;
use App\Models\CvExtraction;

class CvExtractionReviewHandler
{
    public function __construct(
        private CvReviewPayloadValidator $validator,
        private SkillTaxonomyService $skills,
    ) {}

    /** @return array{skills: list<array<string, mixed>>, experiences: list<array<string, mixed>>} */
    public function snapshot(CvExtraction $extraction): array
    {
        $profile = $extraction->cvDocument()->firstOrFail()->candidateProfile()->firstOrFail();

        return [
            'skills' => array_values($profile->candidateSkills()
                ->with('skill:id,name')
                ->orderBy('id')
                ->get()
                ->map(fn (CandidateSkill $candidateSkill): array => [
                    'candidate_skill_id' => $candidateSkill->id,
                    'skill_id' => $candidateSkill->skill_id,
                    'name' => $candidateSkill->skill?->name,
                    'proficiency_level' => $candidateSkill->proficiency_level,
                    'confidence' => $candidateSkill->confidence,
                    'source' => $candidateSkill->source,
                ])->all()),
            'experiences' => array_values($profile->experiences()
                ->orderBy('id')
                ->get(['id', 'job_title', 'company_name', 'start_date', 'end_date', 'source'])
                ->map(fn ($experience): array => $experience->toArray())
                ->all()),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function apply(CvExtraction $extraction, array $payload): array
    {
        $validated = $this->validator->validate($payload);
        $profile = $extraction->cvDocument()->firstOrFail()->candidateProfile()->firstOrFail();

        foreach ($validated['skills'] ?? [] as $skillData) {
            $skill = $this->skills->resolve($skillData['name'])
                ?? $this->skills->saveSkill(null, [
                    'name' => $skillData['name'],
                    'category' => $skillData['category'] ?? 'General',
                ]);

            $profile->candidateSkills()->updateOrCreate(
                ['skill_id' => $skill->id],
                [
                    'source' => ExtractionSource::CV_EXTRACTED->value,
                    'proficiency_level' => $skillData['proficiency_level'] ?? null,
                    'confidence' => $skillData['confidence_score'] ?? $extraction->confidence_score,
                    'evidence' => [
                        'cv_extraction_id' => $extraction->id,
                        'ai_evidence' => $skillData['evidence'] ?? null,
                    ],
                ],
            );
        }

        foreach ($validated['experiences'] ?? [] as $experience) {
            $profile->experiences()->create([
                'job_title' => $experience['title'],
                'company_name' => $experience['company_name'],
                'employment_type' => $experience['employment_type'] ?? null,
                'country' => $experience['country'] ?? null,
                'city' => $experience['city'] ?? null,
                'start_date' => $experience['start_date'] ?? null,
                'end_date' => $experience['end_date'] ?? null,
                'is_current' => $experience['is_current'] ?? false,
                'description' => $experience['description'] ?? null,
                'technologies' => $experience['technologies'] ?? null,
                'source' => ExtractionSource::CV_EXTRACTED->value,
            ]);
        }

        $extractedData = $extraction->extracted_data;
        $extraction->updateQuietly([
            'extracted_data' => [...(is_array($extractedData) ? $extractedData : []), 'verified_payload' => $validated],
        ]);

        return $validated;
    }
}
