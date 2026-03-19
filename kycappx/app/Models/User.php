<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name',
    'username',
    'email',
    'phone',
    'company_name',
    'timezone',
    'theme_preference',
    'status',
    'settings',
    'service_discount_rate',
    'preferred_funding_provider',
    'two_factor_secret',
    'two_factor_confirmed_at',
    'two_factor_recovery_codes',
    'password',
    'last_login_at',
])]
#[Hidden([
    'password',
    'remember_token',
    'two_factor_secret',
    'two_factor_recovery_codes',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'settings' => 'array',
            'service_discount_rate' => 'decimal:2',
            'last_login_at' => 'datetime',
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_recovery_codes' => 'encrypted:array',
        ];
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function fundingRequests(): HasMany
    {
        return $this->hasMany(FundingRequest::class);
    }

    public function verificationRequests(): HasMany
    {
        return $this->hasMany(VerificationRequest::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function dedicatedVirtualAccounts(): HasMany
    {
        return $this->hasMany(DedicatedVirtualAccount::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin', 'support']);
    }

    public function isUserPro(): bool
    {
        return $this->hasRole('user-pro');
    }

    public function hasTwoFactorEnabled(): bool
    {
        return filled($this->two_factor_secret) && $this->two_factor_confirmed_at !== null;
    }

    public function currentDiscountRate(float $default = 0): float
    {
        $userRate = (float) ($this->service_discount_rate ?? 0);

        if ($userRate > 0) {
            return $userRate;
        }

        return $this->isUserPro() ? $default : 0.0;
    }

    public function settingsPayload(): array
    {
        return array_merge([
            'security_alerts' => true,
            'monthly_reports' => true,
            'marketing_emails' => false,
            'login_with_google' => true,
        ], $this->settings ?? []);
    }
}
