<?php

use Illuminate\Support\Facades\Route;
use Modules\Messaging\Http\Controllers\MessageController;

Route::middleware(['auth', 'verified', 'permission:messages.use'])->group(function () {
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages/start', [MessageController::class, 'start'])->name('messages.start');
    Route::get('/messages/{conversation}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{conversation}', [MessageController::class, 'store'])->name('messages.store');
});
