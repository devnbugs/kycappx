<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VerificationRequest extends Model
{
    protected $fillable = [
        'user_id',
        'verification_service_id',
        'reference',
        'status',
        'provider_used',
        'customer_price',
        'provider_cost',
        'request_payload',
        'normalized_response',
        'raw_response',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'customer_price' => 'decimal:2',
            'provider_cost' => 'decimal:2',
            'request_payload' => 'array',
            'normalized_response' => 'array',
            'raw_response' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(VerificationService::class, 'verification_service_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(VerificationAttempt::class);
    }

    public function callbackDeliveries(): HasMany
    {
        return $this->hasMany(CallbackDelivery::class);
    }
}
