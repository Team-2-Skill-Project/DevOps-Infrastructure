<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case APPLIED = 'applied';
    case IN_REVIEW = 'in_review';
    case INTERVIEW = 'interview';
    case REJECTED = 'rejected';
    case OFFER = 'offer';
    case WITHDRAWN = 'withdrawn';
}
