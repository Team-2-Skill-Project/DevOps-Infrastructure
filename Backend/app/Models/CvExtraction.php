<?php

namespace App\Models;

use App\Enums\CvExtractionStatus;
use Database\Factories\CvExtractionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvExtraction extends Model
{
    /** @use HasFactory<CvExtractionFactory> */
    use HasFactory;

    protected $fillable = [
        'cv_document_id',
        'attempt_number',
        'status',
        'provider',
        'model',
        'parser_version',
        'raw_text',
        'extracted_data',
        'confidence_score',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer',
            'extracted_data' => 'array',
            'confidence_score' => 'decimal:4',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'status' => CvExtractionStatus::class,
        ];
    }

    /**
     * @return BelongsTo<CvDocument, $this>
     */
    public function cvDocument(): BelongsTo
    {
        return $this->belongsTo(CvDocument::class);
    }
}
