<?php

namespace App\Enums;

enum AuditEntityType: string
{
    case APPLICATION = 'application';
    case COMPANY = 'company';
    case JOB = 'job';
    case SKILL = 'skill';
    case JOB_SOURCE = 'job_source';
    case INGESTION_RUN = 'ingestion_run';
    case RAW_JOB = 'raw_job';
    case AI_REVIEW_ITEM = 'ai_review_item';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
