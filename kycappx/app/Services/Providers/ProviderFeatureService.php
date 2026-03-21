<?php

namespace App\Services\Providers;

use App\Models\ProviderConfig;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ProviderFeatureService
{
    private array $records = [];

    public function record(string $provider): ?ProviderConfig
    {
        $provider = strtolower($provider);

        if (! array_key_exists($provider, $this->records)) {
            try {
                if (! Schema::hasTable('provider_configs')) {
                    $this->records[$provider] = null;
                } else {
                    $this->records[$provider] = ProviderConfig::query()
                        ->where('provider', $provider)
                        ->first();
                }
            } catch (Throwable) {
                $this->records[$provider] = null;
            }
        }

        return $this->records[$provider];
    }

    public function productCatalog(string $provider): array
    {
        return config("services.$provider.products", []);
    }

    public function isProductEnabled(string $provider, string $product, bool $default = false): bool
    {
        $catalog = $this->productCatalog($provider);
        $fallback = (bool) data_get($catalog, $product.'.required', $default);
        $record = $this->record($provider);

        if (! $record) {
            return $fallback;
        }

        return (bool) data_get($record->config, "enabled_products.$product", $fallback);
    }

    public function enabledProducts(string $provider): array
    {
        return collect($this->productCatalog($provider))
            ->keys()
            ->filter(fn (string $product) => $this->isProductEnabled($provider, $product, false))
            ->values()
            ->all();
    }
}
