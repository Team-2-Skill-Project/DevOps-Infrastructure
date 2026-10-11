<?php

namespace App\Enums;

enum AiReviewAction: string
{
    case APPROVED = 'approved';
    case CORRECTED = 'corrected';
    case REJECTED = 'rejected';
}
