<?php

use Illuminate\Support\Facades\Route;
use Modules\Jobs\Http\Controllers\JobPostingController;
use Modules\Jobs\Http\Controllers\SavedJobController;

Route::get('/jobs', [JobPostingController::class, 'index'])->name('jobs.index');

Route::middleware(['auth', 'verified', 'role:recruiter,admin'])->group(function () {
    Route::get('/jobs/post', [JobPostingController::class, 'create'])->name('jobs.post');
    Route::post('/jobs/post', [JobPostingController::class, 'store'])->name('jobs.store');
    Route::get('/jobs/manage', [JobPostingController::class, 'manage'])->name('jobs.manage');
});

Route::middleware(['auth', 'verified', 'role:student'])->group(function () {
    Route::get('/jobs/saved', [SavedJobController::class, 'index'])->name('jobs.saved');
    Route::post('/jobs/{job}/save', [SavedJobController::class, 'store'])->name('jobs.save');
    Route::delete('/jobs/{job}/save', [SavedJobController::class, 'destroy'])->name('jobs.unsave');
    Route::get('/job-alerts', [SavedJobController::class, 'alerts'])->name('jobs.alerts');
    Route::post('/job-alerts', [SavedJobController::class, 'storeAlert'])->name('jobs.alerts.store');
    Route::delete('/job-alerts/{alert}', [SavedJobController::class, 'destroyAlert'])->name('jobs.alerts.destroy');
});

Route::get('/jobs/{job}', [JobPostingController::class, 'show'])->name('jobs.show');

Route::middleware(['auth', 'verified', 'role:recruiter,admin'])->group(function () {
    Route::get('/jobs/{job}/edit', [JobPostingController::class, 'edit'])->name('jobs.edit');
    Route::put('/jobs/{job}', [JobPostingController::class, 'update'])->name('jobs.update');
    Route::patch('/jobs/{job}/close', [JobPostingController::class, 'close'])->name('jobs.close');
    Route::delete('/jobs/{job}', [JobPostingController::class, 'destroy'])->name('jobs.destroy');
});
