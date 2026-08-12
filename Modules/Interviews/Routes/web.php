<?php

use Illuminate\Support\Facades\Route;
use Modules\Interviews\Http\Controllers\InterviewController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/interviews', [InterviewController::class, 'index'])->name('interviews.index');
    Route::post('/interviews', [InterviewController::class, 'store'])
        ->middleware(['role:recruiter,admin', 'permission:interviews.manage'])
        ->name('interviews.store');
    Route::patch('/interviews/{interview}/status', [InterviewController::class, 'updateStatus'])
        ->name('interviews.update-status');
});
