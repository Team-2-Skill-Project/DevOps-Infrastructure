<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class CvExtractionResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'attempt_number',
        'status',
        'provider',
        'model',
        'parser_version',
        'extracted_data',
        'confidence_score',
        'error_message',
        'started_at',
        'completed_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'cvDocument',
    ];
}
