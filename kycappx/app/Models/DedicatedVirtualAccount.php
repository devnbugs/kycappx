<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DedicatedVirtualAccount extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_reference',
        'customer_reference',
        'account_name',
        'account_number',
        'bank_name',
        'bank_code',
        'currency',
        'status',
        'is_primary',
        'assigned_at',
        'requery_after',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'assigned_at' => 'datetime',
            'requery_after' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
