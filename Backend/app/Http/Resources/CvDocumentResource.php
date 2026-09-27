<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class CvDocumentResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     *
     * @var array|string
     */
    public array $attributes = [
        'original_filename',
        'storage_disk',
        'storage_path',
        'mime_type',
        'file_size',
        'file_hash',
        'version',
        'is_current',
        'status',
        'processed_at',
        'failure_reason',
        'created_at',
        'updated_at',
    ];

    /**
     * The resource's relationships.
     *
     * @var array|string
     */
    public array $relationships = [
        'candidateProfile',
        'extractions',
    ];
}
