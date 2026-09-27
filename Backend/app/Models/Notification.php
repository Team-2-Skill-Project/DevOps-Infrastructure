<?php

namespace App\Models;

use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    /** @use HasFactory<NotificationFactory> */
    use HasFactory;

    public const TYPE_CV_PROCESSING_COMPLETED = 'cv_processing_completed';

    public const TYPE_CV_PROCESSING_FAILED = 'cv_processing_failed';

    public const TYPE_APPLICATION_STATUS_CHANGED = 'application_status_changed';

    public const TYPE_RELEVANT_JOB = 'relevant_job';

    public const TYPE_ROADMAP_REMINDER = 'roadmap_reminder';

    protected $fillable = ['user_id', 'type', 'title', 'message', 'data', 'read_at'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @param Builder<Notification> $query */
    public function scopeUnread(Builder $query): void
    {
        $query->whereNull('read_at');
    }

    /** @param Builder<Notification> $query */
    public function scopeRead(Builder $query): void
    {
        $query->whereNotNull('read_at');
    }

    public function markAsRead(): bool
    {
        if ($this->read_at !== null) {
            return false;
        }

        $this->read_at = now();

        return $this->save();
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function isUnread(): bool
    {
        return ! $this->isRead();
    }
}
