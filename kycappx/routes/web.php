<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProviderManagementController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\VerificationServiceController;
use App\Http\Controllers\Dashboard\ApiKeyController;
use App\Http\Controllers\Dashboard\KoraFundingController;
use App\Http\Controllers\Dashboard\VerificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Webhooks\KoraWebhookController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/wallet', 'wallet')->name('wallet');
        Route::get('/transactions', 'transactions')->name('transactions');
    });

    Route::post('/wallet/fund/kora', [KoraFundingController::class, 'initialize'])->name('wallet.fund.kora');
    Route::get('/wallet/fund/kora/return', [KoraFundingController::class, 'handleReturn'])->name('wallet.fund.kora.return');

    Route::controller(VerificationController::class)->group(function () {
        Route::get('/verifications', 'index')->name('verifications.index');
        Route::get('/verifications/new', 'create')->name('verifications.create');
        Route::post('/verifications', 'store')->name('verifications.store');
    });

    Route::controller(ApiKeyController::class)->group(function () {
        Route::get('/api-keys', 'index')->name('api.keys');
        Route::post('/api-keys', 'store')->name('api.keys.store');
        Route::delete('/api-keys/{apiKey}', 'destroy')->name('api.keys.destroy');
    });

    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::post('/profile/theme', 'updateTheme')->name('profile.theme');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::controller(AdminController::class)->group(function () {
            Route::get('/', 'dashboard')->name('dashboard');
            Route::get('/logs/webhooks', 'webhookLogs')->name('logs.webhooks');
            Route::get('/logs/verifications', 'verificationLogs')->name('logs.verifications');
            Route::get('/logs/audit', 'auditLogs')->name('logs.audit');
        });

        Route::controller(UserManagementController::class)->group(function () {
            Route::get('/users', 'index')->name('users.index');
            Route::get('/customers', 'index')->name('customers.index');
            Route::get('/users/{user}/edit', 'edit')->name('users.edit');
            Route::put('/users/{user}', 'update')->name('users.update');
        });

        Route::controller(VerificationServiceController::class)->group(function () {
            Route::get('/services', 'index')->name('services.index');
            Route::put('/services/{verificationService}', 'update')->name('services.update');
        });

        Route::controller(ProviderManagementController::class)->group(function () {
            Route::get('/providers', 'index')->name('providers.index');
            Route::put('/providers/{providerConfig}', 'update')->name('providers.update');
        });

        Route::controller(SiteSettingsController::class)->group(function () {
            Route::get('/settings/site', 'edit')->name('settings.site');
            Route::put('/settings/site', 'update')->name('settings.site.update');
        });
    });
});

Route::post('/webhooks/kora', [KoraWebhookController::class, 'handle'])
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('webhooks.kora');

require __DIR__.'/auth.php';
