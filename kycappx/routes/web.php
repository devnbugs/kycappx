<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\KoraFundingController;


Route::get('/', function () {
    return view('welcome');
});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/wallet/fund/kora/initialize', [KoraFundingController::class, 'initialize'])
        ->name('wallet.fund.kora.initialize');

    Route::get('/wallet/fund/kora/return', [KoraFundingController::class, 'return'])
        ->name('wallet.fund.kora.return');
});
