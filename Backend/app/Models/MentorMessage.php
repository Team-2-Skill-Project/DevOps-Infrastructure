<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MentorMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentorMessage extends Model
{
    /** @use HasFactory<MentorMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'mentor_chat_id',
        'sender',
        'content',
        'supported_actions',
    ];

    protected $casts = [
        'supported_actions' => 'array',
    ];

    /** @return BelongsTo<MentorChat, $this> */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(MentorChat::class, 'mentor_chat_id');
    }
}
