<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'site_name',
        'site_tagline',
        'support_email',
        'support_phone',
        'default_currency',
        'default_theme',
        'registration_enabled',
        'wallet_funding_enabled',
        'verification_enabled',
        'dark_mode_enabled',
        'google_auth_enabled',
        'dva_enabled',
        'paystack_dva_enabled',
        'kora_dva_enabled',
        'squad_dva_enabled',
        'sms_enabled',
        'user_pro_discount_rate',
        'default_funding_provider',
        'logo_text',
        'header_notice',
        'maintenance_message',
        'footer_text',
    ];

    protected function casts(): array
    {
        return [
            'registration_enabled' => 'boolean',
            'wallet_funding_enabled' => 'boolean',
            'verification_enabled' => 'boolean',
            'dark_mode_enabled' => 'boolean',
            'google_auth_enabled' => 'boolean',
            'dva_enabled' => 'boolean',
            'paystack_dva_enabled' => 'boolean',
            'kora_dva_enabled' => 'boolean',
            'squad_dva_enabled' => 'boolean',
            'sms_enabled' => 'boolean',
            'user_pro_discount_rate' => 'decimal:2',
        ];
    }
}
