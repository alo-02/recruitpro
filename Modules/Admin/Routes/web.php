<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminController;
use Modules\Admin\Http\Controllers\PermissionController;

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');

    Route::middleware('permission:users.manage')->group(function () {
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::patch('/users/{user}/block', [AdminController::class, 'blockUser'])->name('users.block');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    });

    Route::middleware('permission:companies.moderate')->group(function () {
        Route::get('/companies', [AdminController::class, 'companies'])->name('companies');
        Route::patch('/companies/{company}', [AdminController::class, 'moderateCompany'])->name('companies.moderate');
    });

    Route::middleware('permission:jobs.moderate')->group(function () {
        Route::get('/jobs', [AdminController::class, 'jobs'])->name('jobs');
        Route::patch('/jobs/{job}', [AdminController::class, 'moderateJob'])->name('jobs.moderate');
    });

    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    });

    Route::middleware('permission:reports.export')->group(function () {
        Route::get('/reports/export', [AdminController::class, 'exportReport'])->name('reports.export');
        Route::get('/reports/pdf', [AdminController::class, 'exportReportPdf'])->name('reports.pdf');
    });

    Route::middleware('permission:activity.view')->group(function () {
        Route::get('/activity-logs', [AdminController::class, 'activityLogs'])->name('activity-logs');
    });

    Route::middleware('permission:security.view')->group(function () {
        Route::get('/security-events', [AdminController::class, 'securityEvents'])->name('security-events');
        Route::patch('/security-events/{event}', [AdminController::class, 'resolveSecurityEvent'])->name('security-events.resolve');
    });

    Route::middleware('permission:permissions.manage')->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions');
        Route::post('/permissions', [PermissionController::class, 'storePermission'])->name('permissions.store');
        Route::put('/permissions/users/{user}', [PermissionController::class, 'syncUser'])->name('permissions.users.sync');
        Route::put('/permissions/roles/{role}', [PermissionController::class, 'syncRole'])->name('permissions.roles.sync');
    });
});
