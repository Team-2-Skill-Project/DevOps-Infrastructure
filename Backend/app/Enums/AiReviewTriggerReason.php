<?php

namespace App\Enums;

enum AiReviewTriggerReason: string
{
    case LOW_CONFIDENCE = 'low_confidence';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
