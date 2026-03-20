<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\SiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiteSettingsController extends Controller
{
    public function __construct(private SiteSettings $siteSettings)
    {
    }

    public function edit(): View
    {
        return view('admin.settings.site', [
            'settings' => $this->siteSettings->current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $currentSettings = $this->siteSettings->current();
        $booleanSetting = static fn (string $key) => $request->has($key)
            ? $request->boolean($key)
            : (bool) data_get($currentSettings, $key, false);

        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'site_tagline' => ['nullable', 'string', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:50'],
            'default_currency' => ['required', 'string', 'size:3'],
            'default_theme' => ['required', Rule::in(['light', 'dark', 'system'])],
            'registration_enabled' => ['nullable', 'boolean'],
            'wallet_funding_enabled' => ['nullable', 'boolean'],
            'verification_enabled' => ['nullable', 'boolean'],
            'dark_mode_enabled' => ['nullable', 'boolean'],
            'google_auth_enabled' => ['nullable', 'boolean'],
            'dva_enabled' => ['nullable', 'boolean'],
            'paystack_dva_enabled' => ['nullable', 'boolean'],
            'kora_dva_enabled' => ['nullable', 'boolean'],
            'squad_dva_enabled' => ['nullable', 'boolean'],
            'sms_enabled' => ['nullable', 'boolean'],
            'user_pro_discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'default_funding_provider' => ['nullable', Rule::in(['paystack', 'kora', 'squad'])],
            'logo_text' => ['nullable', 'string', 'max:12'],
            'header_notice' => ['nullable', 'string', 'max:255'],
            'maintenance_message' => ['nullable', 'string', 'max:1000'],
            'footer_text' => ['nullable', 'string', 'max:255'],
        ]);

        $settings = $this->siteSettings->update([
            'site_name' => $validated['site_name'],
            'site_tagline' => $validated['site_tagline'] ?? null,
            'support_email' => $validated['support_email'] ?? null,
            'support_phone' => $validated['support_phone'] ?? null,
            'default_currency' => strtoupper($validated['default_currency']),
            'default_theme' => $validated['default_theme'],
            'registration_enabled' => $booleanSetting('registration_enabled'),
            'wallet_funding_enabled' => $booleanSetting('wallet_funding_enabled'),
            'verification_enabled' => $booleanSetting('verification_enabled'),
            'dark_mode_enabled' => $booleanSetting('dark_mode_enabled'),
            'google_auth_enabled' => $booleanSetting('google_auth_enabled'),
            'dva_enabled' => $booleanSetting('dva_enabled'),
            'paystack_dva_enabled' => $booleanSetting('paystack_dva_enabled'),
            'kora_dva_enabled' => $booleanSetting('kora_dva_enabled'),
            'squad_dva_enabled' => $booleanSetting('squad_dva_enabled'),
            'sms_enabled' => $booleanSetting('sms_enabled'),
            'user_pro_discount_rate' => $validated['user_pro_discount_rate'] ?? $currentSettings->user_pro_discount_rate,
            'default_funding_provider' => $validated['default_funding_provider'] ?? $currentSettings->default_funding_provider,
            'logo_text' => strtoupper($validated['logo_text'] ?? $currentSettings->logo_text),
            'header_notice' => $validated['header_notice'] ?? null,
            'maintenance_message' => $validated['maintenance_message'] ?? null,
            'footer_text' => $validated['footer_text'] ?? null,
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'admin.site-settings.updated',
            'target_type' => get_class($settings),
            'target_id' => (string) $settings->id,
            'meta' => [
                'registration_enabled' => $settings->registration_enabled,
                'wallet_funding_enabled' => $settings->wallet_funding_enabled,
                'verification_enabled' => $settings->verification_enabled,
                'dark_mode_enabled' => $settings->dark_mode_enabled,
                'google_auth_enabled' => $settings->google_auth_enabled,
                'dva_enabled' => $settings->dva_enabled,
                'squad_dva_enabled' => $settings->squad_dva_enabled,
                'sms_enabled' => $settings->sms_enabled,
                'default_funding_provider' => $settings->default_funding_provider,
            ],
        ]);

        return redirect()
            ->route('admin.settings.site')
            ->with('status', 'Site settings updated.');
    }
}
