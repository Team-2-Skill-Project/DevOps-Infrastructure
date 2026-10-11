<?php

namespace App\Enums;

enum AiReviewEntityType: string
{
    case CV_EXTRACTION = 'cv_extraction';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
