<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Database\Factories\ApplicationStatusHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationStatusHistory extends Model
{
    /** @use HasFactory<ApplicationStatusHistoryFactory> */
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

    /** @return BelongsTo<Application, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    /** @return BelongsTo<User, $this> */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
