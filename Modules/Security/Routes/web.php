<?php

use Illuminate\Support\Facades\Route;
use Modules\Security\Http\Controllers\TwoFactorController;

Route::middleware('guest')->group(function () {
    Route::get('/two-factor-challenge', [TwoFactorController::class, 'showChallenge'])->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [TwoFactorController::class, 'verify'])
        ->middleware('throttle:10,1')
        ->name('two-factor.verify');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/settings/two-factor/enable', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::post('/settings/two-factor/disable', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
});
