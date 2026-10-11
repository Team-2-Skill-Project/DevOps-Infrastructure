<?php

namespace App\Enums;

enum AuditSource: string
{
    case ADMIN = 'admin';
    case CANDIDATE = 'candidate';
    case SYSTEM = 'system';
    case INGESTION = 'ingestion';
    case AI_REVIEW = 'ai_review';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
