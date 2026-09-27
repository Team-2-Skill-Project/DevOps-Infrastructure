<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_profile_id',
        'job_id',
        'status',
        'cover_letter',
    ];

    protected $casts = [
        'status' => ApplicationStatus::class,
    ];

    public function candidateProfile()
    {
        return $this->belongsTo(CandidateProfile::class);
    }

    public function job()
    {
        return $this->belongsTo(JobPost::class);
    }

    public function histories()
    {
        return $this->hasMany(ApplicationStatusHistory::class);
    }
}
