<?php

use Illuminate\Support\Facades\Route;
use Modules\Api\Http\Controllers\ApiApplicationController;
use Modules\Api\Http\Controllers\ApiAuthController;
use Modules\Api\Http\Controllers\ApiGrowthController;
use Modules\Api\Http\Controllers\ApiJobController;

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'app' => config('app.name'),
    'time' => now()->toIso8601String(),
]));

Route::get('/jobs', [ApiJobController::class, 'index']);
Route::get('/jobs/{job}', [ApiJobController::class, 'show']);
Route::get('/blog', [ApiGrowthController::class, 'blog']);
Route::get('/assessments', [ApiGrowthController::class, 'assessments']);

Route::post('/auth/login', [ApiAuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [ApiAuthController::class, 'me']);
    Route::post('/auth/logout', [ApiAuthController::class, 'logout']);
    Route::get('/applications', [ApiApplicationController::class, 'index']);
    Route::get('/applications/{application}', [ApiApplicationController::class, 'show']);
    Route::get('/recommendations', [ApiGrowthController::class, 'recommendations']);
    Route::get('/offers', [ApiGrowthController::class, 'offers']);
});
