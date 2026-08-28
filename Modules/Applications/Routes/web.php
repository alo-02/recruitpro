<?php

use Illuminate\Support\Facades\Route;
use Modules\Applications\Http\Controllers\ApplicationController;
use Modules\Applications\Http\Controllers\ScreeningController;

Route::middleware(['auth', 'verified', 'role:student'])->group(function () {
    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::post('/jobs/{job}/apply', [ApplicationController::class, 'store'])->name('applications.store');
    Route::get('/applications/success/{job}', [ApplicationController::class, 'success'])->name('applications.success');
    Route::patch('/applications/{application}/withdraw', [ApplicationController::class, 'withdraw'])->name('applications.withdraw');
});

Route::middleware(['auth', 'verified', 'role:recruiter,admin', 'permission:applications.review'])->group(function () {
    Route::get('/recruiter/applications', [ApplicationController::class, 'recruiterIndex'])->name('recruiter.applications');
    Route::patch('/applications/{application}/status', [ApplicationController::class, 'updateStatus'])->name('applications.update-status');
    Route::patch('/applications/{application}/pipeline', [ApplicationController::class, 'updatePipeline'])->name('applications.pipeline');
    Route::post('/applications/{application}/ats-rescan', [ApplicationController::class, 'rescanAts'])->name('applications.ats-rescan');
    Route::post('/jobs/{job}/ats-rescan', [ApplicationController::class, 'rescanJobAts'])->name('jobs.ats-rescan');
    Route::get('/applications/{application}/resume', [ApplicationController::class, 'downloadResume'])->name('applications.resume');
});

Route::get('/applications/{application}/resume/signed', [ApplicationController::class, 'signedResume'])
    ->name('applications.resume.signed')
    ->middleware('signed');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
});

Route::middleware(['auth', 'verified', 'role:recruiter,admin', 'permission:applications.review'])->group(function () {
    Route::get('/recruiter/screening', [ScreeningController::class, 'index'])->name('screening.index');
    Route::post('/jobs/{job}/screening', [ScreeningController::class, 'store'])->name('screening.store');
    Route::get('/recruiter/screening/{session}', [ScreeningController::class, 'show'])->name('screening.show');
    Route::get('/recruiter/screening/{session}/candidates/{result}', [ScreeningController::class, 'candidate'])->name('screening.candidate');
    Route::post('/recruiter/screening/{session}/candidates/{result}/decision', [ScreeningController::class, 'decide'])->name('screening.decide');
    Route::post('/recruiter/screening/{session}/candidates/{result}/interview', [ScreeningController::class, 'pushToInterview'])->name('screening.interview');
});
