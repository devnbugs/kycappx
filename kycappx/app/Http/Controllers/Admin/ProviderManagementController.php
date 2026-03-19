<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ProviderConfig;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProviderManagementController extends Controller
{
    public function index(): View
    {
        $providerHealth = collect([
            [
                'name' => 'Prembly',
                'code' => 'prembly',
                'configured' => filled(config('services.prembly.app_id')) && filled(config('services.prembly.secret_key')),
                'base_url' => config('services.prembly.base_url'),
            ],
            [
                'name' => 'Youverify',
                'code' => 'youverify',
                'configured' => filled(config('services.youverify.token')),
                'base_url' => config('services.youverify.base_url'),
            ],
            [
                'name' => 'Kora',
                'code' => 'kora',
                'configured' => filled(config('services.kora.secret_key')) && filled(config('services.kora.redirect_url')),
                'base_url' => config('services.kora.base_url'),
            ],
        ]);

        return view('admin.providers.index', [
            'providerHealth' => $providerHealth,
            'providerConfigs' => ProviderConfig::query()->orderBy('priority')->get(),
        ]);
    }

    public function update(Request $request, ProviderConfig $providerConfig): RedirectResponse
    {
        $validated = $request->validate([
            'priority' => ['required', 'integer', 'min:1', 'max:99'],
            'channel' => ['nullable', 'string', 'max:100'],
            'mode' => ['nullable', 'string', 'max:50'],
            'timeout_seconds' => ['nullable', 'integer', 'min:5', 'max:120'],
            'notes' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $config = array_filter([
            'channel' => $validated['channel'] ?? null,
            'mode' => $validated['mode'] ?? null,
            'timeout_seconds' => $validated['timeout_seconds'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        $providerConfig->update([
            'priority' => $validated['priority'],
            'is_active' => $request->boolean('is_active'),
            'config' => $config,
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'admin.provider.updated',
            'target_type' => ProviderConfig::class,
            'target_id' => (string) $providerConfig->id,
            'meta' => [
                'provider' => $providerConfig->provider,
                'priority' => $providerConfig->priority,
                'is_active' => $providerConfig->is_active,
            ],
        ]);

        return redirect()
            ->route('admin.providers.index')
            ->with('status', strtoupper($providerConfig->provider).' provider settings updated.');
    }
}
