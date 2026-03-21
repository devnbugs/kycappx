<?php

namespace App\Providers;

use App\Models\VerificationService;
use App\Services\SiteSettings;
use App\Services\Verification\VerificationCatalogService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
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
    public function boot(SiteSettings $siteSettings, VerificationCatalogService $verificationCatalog): void
    {
        $settings = $siteSettings->current();

        config(['app.name' => $settings->site_name ?: config('app.name')]);

        View::share('siteSettings', $settings);
        View::share('workspaceServices', $this->workspaceServices($verificationCatalog));
    }

    private function workspaceServices(VerificationCatalogService $verificationCatalog)
    {
        try {
            if (! Schema::hasTable('verification_services')) {
                return collect();
            }
        } catch (Throwable) {
            return collect();
        }

        return $verificationCatalog
            ->filterFeatured($verificationCatalog->filterLaunchable(
                VerificationService::query()
                    ->active()
                    ->orderBy('name')
                    ->get(['id', 'code', 'name', 'type', 'is_active', 'default_price'])
            ))
            ->take(10)
            ->values();
    }
}
