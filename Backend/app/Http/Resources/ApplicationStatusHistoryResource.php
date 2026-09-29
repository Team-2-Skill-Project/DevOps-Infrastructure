<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ApplicationStatusHistoryResource extends JsonApiResource
{
    /** @var array<string> */
    public array $attributes = [
        'old_status',
        'new_status',
        'notes',
        'created_at',
    ];

    /** @var array<string> */
    public array $relationships = [
        'application',
        'changer',
    ];
}
