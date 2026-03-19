<?php

use App\Http\Controllers\Admin\AdminController;
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
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    Route::prefix('admin')->name('admin.')->middleware('admin')->controller(AdminController::class)->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/customers', 'customers')->name('customers.index');
        Route::get('/services', 'services')->name('services.index');
        Route::get('/providers', 'providers')->name('providers.index');
        Route::get('/logs/webhooks', 'webhookLogs')->name('logs.webhooks');
        Route::get('/logs/verifications', 'verificationLogs')->name('logs.verifications');
    });
});

Route::post('/webhooks/kora', [KoraWebhookController::class, 'handle'])
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('webhooks.kora');

require __DIR__.'/auth.php';
