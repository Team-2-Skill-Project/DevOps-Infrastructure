<?php

namespace App\Models;

use App\Enums\CvParsingStatus;
use Database\Factories\CvDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $user_id
 * @property string $file_path
 * @property string|null $raw_extracted_text
 */
class CvDocument extends Model
{
    /** @use HasFactory<CvDocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'candidate_profile_id',
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
    ];

    /**
     * @property int $id
     * @property int $candidate_profile_id
     * @property int $user_id
     * @property string $storage_path
     * @property string|null $raw_extracted_text
     */

    /**
     * @return array{file_size: 'integer', version: 'integer', is_current: 'boolean', processed_at: 'datetime'}
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'version' => 'integer',
            'is_current' => 'boolean',
            'processed_at' => 'datetime',
            'status' => CvParsingStatus::class,
            'raw_extracted_text' => 'string',
            'user_id' => 'integer',
        ];
    }

    /** @return BelongsTo<CandidateProfile, $this> */
    public function candidateProfile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class);
    }

    /** @return HasMany<CvExtraction, $this> */
    public function extractions(): HasMany
    {
        return $this->hasMany(CvExtraction::class);
    }
}
