<?php

namespace App\Providers;

use App\Services\SiteSettings;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
    }
}
