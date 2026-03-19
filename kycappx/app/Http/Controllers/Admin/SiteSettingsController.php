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
            'registration_enabled' => $request->boolean('registration_enabled'),
            'wallet_funding_enabled' => $request->boolean('wallet_funding_enabled'),
            'verification_enabled' => $request->boolean('verification_enabled'),
            'dark_mode_enabled' => $request->boolean('dark_mode_enabled'),
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
            ],
        ]);

        return redirect()
            ->route('admin.settings.site')
            ->with('status', 'Site settings updated.');
    }
}
