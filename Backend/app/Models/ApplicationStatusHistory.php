<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationStatusHistory extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'application_id',
        'changed_by',
        'old_status',
        'new_status',
        'notes',
    ];

    protected $casts = [
        'old_status' => ApplicationStatus::class,
        'new_status' => ApplicationStatus::class,
    ];

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
