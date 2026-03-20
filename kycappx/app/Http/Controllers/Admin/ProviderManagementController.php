<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ProviderConfig;
use App\Services\Providers\ProviderFeatureService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProviderManagementController extends Controller
{
    public function __construct(private ProviderFeatureService $providerFeatures)
    {
    }

    public function index(): View
    {
        $providerHealth = collect([
            [
                'name' => 'Prembly',
                'code' => 'prembly',
                'configured' => filled(config('services.prembly.app_id')) && filled(config('services.prembly.secret_key')),
                'base_url' => config('services.prembly.base_url'),
                'products' => count(config('services.prembly.products', [])),
                'enabled_products' => count($this->providerFeatures->enabledProducts('prembly')),
            ],
            [
                'name' => 'Kora',
                'code' => 'kora',
                'configured' => filled(config('services.kora.secret_key')),
                'base_url' => config('services.kora.base_url'),
                'products' => count(config('services.kora.products', [])),
                'enabled_products' => count($this->providerFeatures->enabledProducts('kora')),
            ],
            [
                'name' => 'Squad',
                'code' => 'squad',
                'configured' => filled(config('services.squad.secret_key')),
                'base_url' => config('services.squad.base_url'),
                'products' => count(config('services.squad.products', [])),
                'enabled_products' => count($this->providerFeatures->enabledProducts('squad')),
            ],
            [
                'name' => 'Paystack',
                'code' => 'paystack',
                'configured' => filled(config('services.paystack.secret_key')),
                'base_url' => config('services.paystack.base_url'),
                'products' => count(config('services.paystack.products', [])),
                'enabled_products' => count($this->providerFeatures->enabledProducts('paystack')),
            ],
        ]);

        return view('admin.providers.index', [
            'providerHealth' => $providerHealth,
            'providerConfigs' => ProviderConfig::query()->orderBy('priority')->get(),
        ]);
    }

    public function update(Request $request, ProviderConfig $providerConfig): RedirectResponse
    {
        $catalog = config("services.{$providerConfig->provider}.products", []);

        $validated = $request->validate([
            'priority' => ['required', 'integer', 'min:1', 'max:99'],
            'channel' => ['nullable', 'string', 'max:100'],
            'mode' => ['nullable', 'string', 'max:50'],
            'timeout_seconds' => ['nullable', 'integer', 'min:5', 'max:120'],
            'notes' => ['nullable', 'string', 'max:255'],
            'default_product' => ['nullable', Rule::in(array_keys($catalog))],
            'country_scope' => ['nullable', 'array'],
            'country_scope.*' => ['nullable', 'string', 'size:2'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $enabledProducts = collect(array_keys($catalog))
            ->mapWithKeys(fn (string $product) => [$product => $request->boolean("enabled_products.$product")])
            ->all();

        $config = array_filter([
            'channel' => $validated['channel'] ?? null,
            'mode' => $validated['mode'] ?? null,
            'timeout_seconds' => $validated['timeout_seconds'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'default_product' => $validated['default_product'] ?? null,
            'country_scope' => collect($validated['country_scope'] ?? [])
                ->filter()
                ->map(fn (string $country) => strtoupper($country))
                ->values()
                ->all(),
            'enabled_products' => $enabledProducts,
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
                'enabled_products' => $enabledProducts,
            ],
        ]);

        return redirect()
            ->route('admin.providers.index')
            ->with('status', strtoupper($providerConfig->provider).' provider settings updated.');
    }
}
