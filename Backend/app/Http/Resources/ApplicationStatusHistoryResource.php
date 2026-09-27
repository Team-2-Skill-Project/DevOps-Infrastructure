<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ApplicationStatusHistoryResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     *
     * @var array|string
     */
    public array $attributes = [
        'old_status',
        'new_status',
        'notes',
        'created_at',
    ];

    /**
     * The resource's relationships.
     *
     * @var array|string
     */
    public array $relationships = [
        'application',
        'changer',
    ];
}
