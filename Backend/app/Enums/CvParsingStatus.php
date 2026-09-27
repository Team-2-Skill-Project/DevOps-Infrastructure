<?php

namespace App\Enums;

enum CvParsingStatus: string
{
    case UPLOADED = 'uploaded';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
