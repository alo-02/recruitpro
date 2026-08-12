<?php

use Illuminate\Support\Facades\Route;
use Modules\Privacy\Http\Controllers\PrivacyController;

Route::middleware(['auth', 'verified'])->prefix('privacy')->name('privacy.')->group(function () {
    Route::get('/', [PrivacyController::class, 'index'])->name('index');
    Route::get('/export', [PrivacyController::class, 'export'])->name('export');
    Route::delete('/account', [PrivacyController::class, 'destroy'])->name('destroy');
});
