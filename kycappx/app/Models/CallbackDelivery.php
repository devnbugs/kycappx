<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallbackDelivery extends Model
{
    protected $fillable = [
        'verification_request_id',
        'url',
        'attempts',
        'status',
        'last_response',
        'last_attempt_at',
    ];

    protected function casts(): array
    {
        return [
            'last_response' => 'array',
            'last_attempt_at' => 'datetime',
        ];
    }

    public function verificationRequest(): BelongsTo
    {
        return $this->belongsTo(VerificationRequest::class);
    }
}
