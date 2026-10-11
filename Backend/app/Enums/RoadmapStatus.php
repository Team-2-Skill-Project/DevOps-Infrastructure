<?php

namespace App\Enums;

enum RoadmapStatus: string
{
    case ACTIVE = 'active';

    case COMPLETED = 'completed';

    case ARCHIVED = 'archived';
}
