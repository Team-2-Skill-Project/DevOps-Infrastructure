<?php

namespace App\Enums;

enum ExtractionSource: string
{
    case CV_EXTRACTED = 'cv_extracted';
    case MANUAL = 'manual';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
