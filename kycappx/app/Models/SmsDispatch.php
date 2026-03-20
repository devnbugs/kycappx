<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsDispatch extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'action',
        'sender_id',
        'reference',
        'remote_reference',
        'status',
        'title',
        'message',
        'recipients',
        'request_payload',
        'response_payload',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'recipients' => 'array',
            'request_payload' => 'array',
            'response_payload' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
