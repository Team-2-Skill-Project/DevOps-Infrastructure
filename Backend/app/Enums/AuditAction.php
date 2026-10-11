<?php

namespace App\Enums;

enum AuditAction: string
{
    case COMPANY_CREATED = 'company_created';
    case COMPANY_UPDATED = 'company_updated';
    case COMPANY_MERGED = 'company_merged';
    case JOB_CREATED = 'job_created';
    case JOB_UPDATED = 'job_updated';
    case SKILL_CREATED = 'skill_created';
    case SKILL_UPDATED = 'skill_updated';
    case SKILL_ALIAS_CREATED = 'skill_alias_created';
    case SKILL_ALIAS_UPDATED = 'skill_alias_updated';
    case SKILL_ALIAS_DELETED = 'skill_alias_deleted';
    case SKILL_MERGED = 'skill_merged';
    case JOB_SOURCE_CREATED = 'job_source_created';
    case JOB_SOURCE_UPDATED = 'job_source_updated';
    case APPLICATION_CREATED = 'application_created';
    case APPLICATION_STATUS_CHANGED = 'application_status_changed';
    case INGESTION_RUN_STARTED = 'ingestion_run_started';
    case INGESTION_RUN_FINISHED = 'ingestion_run_finished';
    case RAW_JOB_EXTRACTION_FAILED = 'raw_job_extraction_failed';
    case JOB_NORMALIZED = 'job_normalized';
    case JOB_NORMALIZATION_FAILED = 'job_normalization_failed';
    case JOB_DEDUPLICATED = 'job_deduplicated';
    case AI_REVIEW_APPROVED = 'ai_review_approved';
    case AI_REVIEW_CORRECTED = 'ai_review_corrected';
    case AI_REVIEW_REJECTED = 'ai_review_rejected';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
