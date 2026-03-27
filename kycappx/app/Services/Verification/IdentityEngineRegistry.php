<?php

namespace App\Services\Verification;

use App\Models\VerificationService;
use App\Services\Providers\ProviderFeatureService;
use Illuminate\Support\Str;

class IdentityEngineRegistry
{
    public function __construct(private ProviderFeatureService $providerFeatures)
    {
    }

    public function providers(): array
    {
        return collect(config('verification_engines.providers', []))
            ->mapWithKeys(fn (array $provider, string $code) => [
                strtolower($code) => array_merge([
                    'adminLabel' => Str::headline($code),
                    'publicLabel' => 'v0',
                    'productToggle' => null,
                ], $provider),
            ])
            ->all();
    }

    public function providerCodes(): array
    {
        return array_keys($this->providers());
    }

    public function adminLabel(string $provider): string
    {
        return (string) data_get($this->providers(), strtolower($provider).'.adminLabel', Str::headline($provider));
    }

    public function publicLabel(string $provider): string
    {
        return (string) data_get(
            $this->providerFeatures->record(strtolower($provider))?->config,
            'publicLabel',
            data_get($this->providers(), strtolower($provider).'.publicLabel', 'v0')
        );
    }

    public function serviceCatalogAdditions(): array
    {
        return config('verification_engines.serviceCatalog', []);
    }

    public function defaultServicePreferences(VerificationService|string $service): array
    {
        $code = $this->serviceCode($service);
        $defaults = collect(data_get(config('verification_engines.serviceDefaults'), $code.'.enginePreferences', []))
            ->map(fn (string $provider) => strtolower($provider))
            ->filter()
            ->unique()
            ->values();

        if ($defaults->isNotEmpty()) {
            return $defaults->all();
        }

        return $this->premblyRouteForService($code) ? ['prembly'] : [];
    }

    public function enginePreferences(VerificationService $service): array
    {
        $stored = collect($service->engine_preferences ?? [])
            ->map(fn (string $provider) => strtolower($provider))
            ->filter()
            ->unique()
            ->values();

        if ($stored->isNotEmpty()) {
            return $stored->all();
        }

        return $this->defaultServicePreferences($service);
    }

    public function responseTemplate(VerificationService|string $service): string
    {
        if ($service instanceof VerificationService && filled($service->response_template)) {
            return (string) $service->response_template;
        }

        return (string) data_get(
            config('verification_engines.serviceDefaults'),
            $this->serviceCode($service).'.responseTemplate',
            'auto'
        );
    }

    public function supportedProvidersForService(VerificationService|string $service): array
    {
        return collect($this->providerCodes())
            ->filter(fn (string $provider) => $this->routeForService($provider, $service) !== null)
            ->values()
            ->all();
    }

    public function availableProvidersForService(VerificationService $service): array
    {
        return collect($this->enginePreferences($service))
            ->filter(fn (string $provider) => $this->providerAvailableForService($provider, $service))
            ->values()
            ->all();
    }

    public function providerAvailableForService(string $provider, VerificationService|string $service): bool
    {
        $provider = strtolower($provider);
        $route = $this->routeForService($provider, $service);

        if (! $route || ! $this->providerConfigured($provider) || ! $this->providerActive($provider)) {
            return false;
        }

        $productToggle = $route['productToggle'] ?? data_get($this->providers(), $provider.'.productToggle');

        if ($provider === 'prembly' && filled($route['productKey'] ?? null)) {
            return $this->providerFeatures->isProductEnabled($provider, (string) $route['productKey'], true);
        }

        if (! $productToggle) {
            return true;
        }

        return $this->providerFeatures->isProductEnabled($provider, (string) $productToggle, true);
    }

    public function routeForService(string $provider, VerificationService|string $service): ?array
    {
        $provider = strtolower($provider);
        $code = $this->serviceCode($service);
        $route = $this->defaultRouteForService($provider, $code);
        $override = $this->providerRouteOverrides($provider, $code);

        if ($override !== []) {
            $productKey = (string) ($override['productKey'] ?? $route['productKey'] ?? '');
            $catalogRoute = $productKey !== '' ? $this->routeCatalog($provider, $productKey) : [];
            $route = array_merge($route, $catalogRoute, $override);
        }

        if ($route === []) {
            return null;
        }

        if (($route['endpointPath'] ?? null) === '' || ($route['endpointPath'] ?? null) === null) {
            if ($provider !== 'prembly') {
                return null;
            }
        }

        $route['provider'] = $provider;
        $route['serviceCode'] = $code;

        return $route;
    }

    public function providerConfigured(string $provider): bool
    {
        return match (strtolower($provider)) {
            'prembly' => filled(config('services.prembly.app_id')) && filled(config('services.prembly.secret_key')),
            'kora' => filled(config('services.kora.secret_key')),
            'interswitch' => filled(config('services.interswitch.client_id')) && filled(config('services.interswitch.client_secret')),
            default => false,
        };
    }

    public function providerActive(string $provider): bool
    {
        $record = $this->providerFeatures->record(strtolower($provider));

        return $record ? (bool) $record->is_active : $this->providerConfigured($provider);
    }

    public function providerOptions(): array
    {
        return collect($this->providerCodes())
            ->mapWithKeys(fn (string $provider) => [$provider => $this->adminLabel($provider)])
            ->all();
    }

    private function defaultRouteForService(string $provider, string $serviceCode): array
    {
        $routeOverride = data_get(config('verification_engines.serviceDefaults'), $serviceCode.'.routeOverrides.'.$provider, []);

        if ($provider === 'prembly') {
            $premblyRoute = $this->premblyRouteForService($serviceCode);

            if ($premblyRoute !== []) {
                return array_merge($premblyRoute, $routeOverride);
            }
        }

        $productKey = (string) data_get($routeOverride, 'productKey', '');

        if ($productKey !== '') {
            return array_merge($this->routeCatalog($provider, $productKey), $routeOverride);
        }

        return [];
    }

    private function premblyRouteForService(string $serviceCode): array
    {
        return collect(config('services.prembly.products', []))
            ->mapWithKeys(function (array $definition, string $productKey) {
                $code = strtoupper((string) data_get($definition, 'service.code', $productKey));

                return [$code => array_merge([
                    'productKey' => $productKey,
                    'productToggle' => $productKey,
                    'requestMethod' => data_get($definition, 'method', 'POST'),
                    'endpointPath' => data_get($definition, 'path'),
                    'normalizer' => data_get($definition, 'normalizer'),
                    'definition' => $definition,
                ], $definition)];
            })
            ->get($serviceCode, []);
    }

    private function providerRouteOverrides(string $provider, string $serviceCode): array
    {
        $record = $this->providerFeatures->record($provider);
        $override = data_get($record?->config, 'verificationRoutes.'.$serviceCode, []);

        return is_array($override) ? $override : [];
    }

    private function routeCatalog(string $provider, string $productKey): array
    {
        $catalog = data_get(config('verification_engines.routeCatalog'), strtolower($provider).'.'.$productKey, []);

        return is_array($catalog) ? $catalog : [];
    }

    private function serviceCode(VerificationService|string $service): string
    {
        return strtoupper($service instanceof VerificationService ? $service->code : $service);
    }
}
