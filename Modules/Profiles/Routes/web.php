<?php

use Illuminate\Support\Facades\Route;
use Modules\Profiles\Http\Controllers\ProfileController;

Route::middleware(['auth', 'verified', 'role:student'])->group(function () {
    Route::get('/profile/builder', [ProfileController::class, 'edit'])->name('profile.builder');
    Route::put('/profile/builder', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified', 'role:recruiter,admin'])->group(function () {
    Route::get('/candidates/{user}', [ProfileController::class, 'show'])->name('candidates.show');
});
