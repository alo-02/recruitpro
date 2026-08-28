<?php

use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'home'])->name('home');
Route::get('/media/{path}', [MediaController::class, 'show'])->where('path', '.*')->name('media.show');

Route::middleware(['auth', 'verified', 'role:student'])->group(function () {
    Route::get('/student/dashboard', [DashboardController::class, 'student'])->name('student.dashboard');
});

Route::middleware(['auth', 'verified', 'role:recruiter,admin'])->group(function () {
    Route::get('/recruiter/dashboard', [DashboardController::class, 'recruiter'])->name('recruiter.dashboard');
    Route::get('/candidates/review', [DashboardController::class, 'candidateReview'])->name('candidates.review');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
    Route::put('/settings', [DashboardController::class, 'updateSettings'])->name('settings.update');
    Route::post('/notifications/{id}/read', [DashboardController::class, 'markNotificationRead'])->name('notifications.read');
});
