<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MentorChatFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MentorChat extends Model
{
    /** @use HasFactory<MentorChatFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_id',
        'title',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<JobPost, $this> */
    public function job(): BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'job_id');
    }

    /** @return HasMany<MentorMessage, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(MentorMessage::class);
    }
}
