<?php

namespace App\Http\Resources;

use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\Certificate;
use App\Models\CvDocument;
use App\Models\CvExtraction;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Language;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CandidateProfile */
class CandidateProfileResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            ...$this->only([
                'id', 'date_of_birth', 'gender', 'job_title', 'country', 'state', 'city',
                'github_url', 'linkedin_url', 'military_status', 'professional_summary',
                'profile_completed_at',
            ]),
            'careerPreference' => $this->careerPreference?->only([
                'id', 'target_role', 'job_type', 'work_mode', 'preferred_country',
                'preferred_city', 'experience_level', 'career_goal', 'open_to_relocation', 'target_roles', 'preferred_industries',
            ]),
            'educations' => $this->educations->map(fn (Education $education): array => $education->only([
                'id', 'education_level', 'institution', 'field_of_study', 'degree',
                'start_date', 'end_date', 'is_current', 'grade', 'description', 'source',
            ])),
            'experiences' => $this->experiences->map(fn (Experience $experience): array => $experience->only([
                'id', 'job_title', 'company_name', 'employment_type', 'country', 'city',
                'start_date', 'end_date', 'is_current', 'description', 'source', 'technologies',
            ])),
            'projects' => $this->projects->map(fn (Project $project): array => $project->only([
                'id', 'name', 'description', 'technologies', 'project_url', 'github_url',
                'start_date', 'end_date', 'source',
            ])),
            'certificates' => $this->certificates->map(fn (Certificate $certificate): array => $certificate->only([
                'id', 'name', 'issuer', 'issue_date', 'expiration_date', 'credential_id',
                'credential_url', 'source',
            ])),
            'languages' => $this->languages->map(fn (Language $language): array => $language->only([
                'id', 'language', 'proficiency_level', 'source',
            ])),
            'candidateSkills' => $this->candidateSkills->map(fn (CandidateSkill $candidateSkill): array => [
                ...$candidateSkill->only(['id', 'skill_id', 'source', 'proficiency_level']),
                'skill' => $candidateSkill->skill?->only(['id', 'name', 'normalized_name', 'category']),
            ]),
            'cvDocuments' => $this->cvDocuments->map(fn (CvDocument $document): array => [
                ...$document->only([
                    'id', 'original_filename', 'mime_type', 'file_size', 'version',
                    'is_current', 'status', 'processed_at', 'created_at', 'updated_at',
                ]),
                'extractions' => $document->extractions->map(fn (CvExtraction $extraction): array => $extraction->only([
                    'id', 'attempt_number', 'status', 'confidence_score', 'started_at', 'completed_at',
                ]))->values()->all(),
            ]),
        ];
    }
}
