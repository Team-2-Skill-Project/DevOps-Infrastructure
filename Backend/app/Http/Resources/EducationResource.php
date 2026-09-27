<?php

namespace App\Http\Resources;

use App\Models\Education;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/** @mixin Education */
class EducationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            ...$this->only([
                'id', 'education_level', 'institution', 'field_of_study', 'degree',
                'is_current', 'grade', 'description', 'source',
            ]),
            'start_date' => $this->start_date ? Carbon::parse($this->start_date)->toDateString() : null,
            'end_date' => $this->end_date ? Carbon::parse($this->end_date)->toDateString() : null,
        ];
    }
}
