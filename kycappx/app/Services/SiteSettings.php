<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SiteSettings
{
    private const CACHE_KEY = 'site_settings.current';

    public function current(): SiteSetting
    {
        if (! $this->tableExists()) {
            return new SiteSetting($this->defaults());
        }

        $cached = Cache::get(self::CACHE_KEY);

        if (is_array($cached)) {
            return new SiteSetting($cached);
        }

        if ($cached instanceof SiteSetting) {
            return $cached;
        }

        Cache::forget(self::CACHE_KEY);

        $settings = SiteSetting::query()->first()
            ?? SiteSetting::query()->create($this->defaults());

        Cache::forever(self::CACHE_KEY, $settings->getAttributes());

        return $settings;
    }

    public function update(array $attributes): SiteSetting
    {
        if (! $this->tableExists()) {
            throw new \RuntimeException('Site settings table is not available yet.');
        }

        $settings = SiteSetting::query()->first() ?? new SiteSetting($this->defaults());
        $settings->fill($attributes);
        $settings->save();

        Cache::forget(self::CACHE_KEY);
        Cache::forever(self::CACHE_KEY, $settings->getAttributes());

        return new SiteSetting($settings->getAttributes());
    }

    public function defaults(): array
    {
        return [
            'site_name' => config('app.name', 'Kycappx'),
            'site_tagline' => 'Verification, wallet operations, and API control from one workspace.',
            'support_email' => 'support@kycappx.local',
            'support_phone' => null,
            'default_currency' => 'NGN',
            'default_theme' => 'system',
            'registration_enabled' => true,
            'wallet_funding_enabled' => true,
            'verification_enabled' => true,
            'dark_mode_enabled' => true,
            'google_auth_enabled' => true,
            'dva_enabled' => true,
            'paystack_dva_enabled' => true,
            'kora_dva_enabled' => true,
            'user_pro_discount_rate' => 10,
            'default_funding_provider' => 'paystack',
            'logo_text' => 'KX',
            'header_notice' => 'Built for fast onboarding, wallet funding, and identity checks.',
            'maintenance_message' => 'We are tuning the platform for better performance and reliability.',
            'footer_text' => 'Secure identity and wallet operations from one workspace.',
        ];
    }

    private function tableExists(): bool
    {
        try {
            return Schema::hasTable('site_settings');
        } catch (Throwable) {
            return false;
        }
    }
}
