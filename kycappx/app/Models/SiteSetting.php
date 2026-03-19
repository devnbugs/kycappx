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
        ];
    }
}
