<?php

namespace App\Enums;

enum RoadmapTaskStatus: string
{
    case PENDING = 'pending';

    case IN_PROGRESS = 'in_progress';

    case COMPLETED = 'completed';
}
