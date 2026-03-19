<?php

namespace App\Providers;

use App\Models\VerificationService;
use App\Services\SiteSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(SiteSettings $siteSettings): void
    {
        $settings = $siteSettings->current();

        config(['app.name' => $settings->site_name ?: config('app.name')]);

        View::share('siteSettings', $settings);
        View::share('workspaceServices', $this->workspaceServices());
    }

    private function workspaceServices()
    {
        try {
            if (! Schema::hasTable('verification_services')) {
                return collect();
            }
        } catch (Throwable) {
            return collect();
        }

        $services = Cache::remember(
            'workspace.services.navigation',
            now()->addMinutes(5),
            fn () => VerificationService::query()
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'is_active', 'default_price'])
        );

        // Filter to ensure we only return valid objects
        return collect($services)->filter(fn ($service) => is_object($service))->values();
    }
}
