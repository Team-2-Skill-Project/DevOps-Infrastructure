<?php

namespace App\Http\Resources;

use App\Models\Experience;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/** @mixin Experience */
class ExperienceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            ...$this->only([
                'id', 'job_title', 'company_name', 'employment_type', 'country', 'city',
                'is_current', 'description', 'source', 'technologies',
            ]),
            'start_date' => $this->start_date ? Carbon::parse($this->start_date)->toDateString() : null,
            'end_date' => $this->end_date ? Carbon::parse($this->end_date)->toDateString() : null,
        ];
    }
}
