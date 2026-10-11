<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class JobSkillPivot extends Pivot
{
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'importance' => 'integer',
        ];
    }
}
