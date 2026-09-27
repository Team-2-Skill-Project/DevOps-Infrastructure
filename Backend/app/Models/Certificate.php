<?php

namespace App\Models;

use Database\Factories\CertificateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    /** @use HasFactory<CertificateFactory> */
    use HasFactory;

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_CV_EXTRACTED = 'cv_extracted';

    protected $fillable = [
        'candidate_profile_id',
        'name',
        'issuer',
        'issue_date',
        'expiration_date',
        'credential_id',
        'credential_url',
        'source',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiration_date' => 'date',
        ];
    }

    /** @return BelongsTo<CandidateProfile, $this> */
    public function candidateProfile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class);
    }
}
