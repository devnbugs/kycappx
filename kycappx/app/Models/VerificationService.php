<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VerificationService extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'country',
        'is_active',
        'default_price',
        'default_cost',
        'required_fields',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'default_price' => 'decimal:2',
            'default_cost' => 'decimal:2',
            'required_fields' => 'array',
        ];
    }

    public function verificationRequests(): HasMany
    {
        return $this->hasMany(VerificationRequest::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
