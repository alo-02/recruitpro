<?php

use Illuminate\Support\Facades\Route;
use Modules\Companies\Http\Controllers\CompanyController;

Route::middleware(['auth', 'verified', 'role:recruiter'])->group(function () {
    Route::get('/company/profile', [CompanyController::class, 'edit'])->name('company.profile');
    Route::put('/company/profile', [CompanyController::class, 'update'])->name('company.update');
});
