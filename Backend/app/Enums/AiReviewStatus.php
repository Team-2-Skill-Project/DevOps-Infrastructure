<?php

namespace App\Enums;

enum AiReviewStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case CORRECTED = 'corrected';
    case REJECTED = 'rejected';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
