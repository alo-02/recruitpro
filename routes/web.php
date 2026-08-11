<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobPostingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [DashboardController::class, 'home'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/register', [AuthController::class, 'register'])->name('register')->middleware('throttle:5,1');
    Route::get('/password-reset', [AuthController::class, 'showPasswordReset'])->name('password.reset');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Public job browsing
Route::get('/jobs', [JobPostingController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{job}', [JobPostingController::class, 'show'])->name('jobs.show');

// Student routes
Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/student/dashboard', [DashboardController::class, 'student'])->name('student.dashboard');
    Route::get('/profile/builder', [ProfileController::class, 'edit'])->name('profile.builder');
    Route::put('/profile/builder', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::post('/jobs/{job}/apply', [ApplicationController::class, 'store'])->name('applications.store');
    Route::get('/applications/success/{job}', [ApplicationController::class, 'success'])->name('applications.success');
});

// Recruiter routes
Route::middleware(['auth', 'role:recruiter,admin'])->group(function () {
    Route::get('/recruiter/dashboard', [DashboardController::class, 'recruiter'])->name('recruiter.dashboard');
    Route::get('/jobs/post', [JobPostingController::class, 'create'])->name('jobs.post');
    Route::post('/jobs/post', [JobPostingController::class, 'store'])->name('jobs.store');
    Route::get('/jobs/manage', [JobPostingController::class, 'manage'])->name('jobs.manage');
    Route::patch('/jobs/{job}/close', [JobPostingController::class, 'close'])->name('jobs.close');
    Route::get('/talent-pool', [DashboardController::class, 'talentPool'])->name('talent-pool');
    Route::get('/recruiter/applications', [ApplicationController::class, 'recruiterIndex'])->name('recruiter.applications');
    Route::patch('/applications/{application}/status', [ApplicationController::class, 'updateStatus'])->name('applications.update-status');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [DashboardController::class, 'admin'])->name('admin.index');
});

// Shared authenticated routes
Route::middleware('auth')->group(function () {
    Route::view('/messages', 'messages.index')->name('messages.index');
    Route::view('/interviews', 'recruiter.interviews')->name('interviews.index');
    Route::view('/candidates/review', 'recruiter.candidate-review')->name('candidates.review');
    Route::view('/settings', 'settings.index')->name('settings');
});
