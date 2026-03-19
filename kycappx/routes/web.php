<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
        // User dashboard
    Route::get('/dashboard', function () {
        return view('dashboard.home');
    })->name('dashboard');
    Route::get('/wallet', function () {
        return view('dashboard.wallet');
    })->name('wallet');
    Route::get('/transactions', function () {
        return view('dashboard.transactions');
    })->name('transactions');

    Route::get('/verifications', function () {
        return view('dashboard.verifications.index');
    })->name('verifications.index');
    Route::get('/verifications/new', function () {
        return view('dashboard.verifications.create');
    })->name('verifications.create');

    Route::get('/api-keys', function () {
        return view('dashboard.api-keys');
    })->name('api.keys');

    // Admin dashboard
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/', function () {
            return view('admin.home');
        })->name('admin.home');

        Route::get('/customers', function () {
            return view('admin.customers.index');
        })->name('admin.customers');
        Route::get('/services', function () {
            return view('admin.services.index');
        })->name('admin.services');
        Route::get('/providers', function () {
            return view('admin.providers.index');
        })->name('admin.providers');

        Route::get('/logs/webhooks', function () {
            return view('admin.logs.webhooks');
        })->name('admin.logs.webhooks');
        Route::get('/logs/verifications', function () {
            return view('admin.logs.verifications');
        })->name('admin.logs.verifications');
    });
});

require __DIR__.'/auth.php';
