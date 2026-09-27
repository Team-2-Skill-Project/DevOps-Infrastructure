<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ApplicationResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'status',
        'cover_letter',
        'applied_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'job',
        'candidateProfile',
        'histories',
    ];
}
