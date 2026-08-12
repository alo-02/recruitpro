<?php

use Illuminate\Support\Facades\Route;
use Modules\Growth\Http\Controllers\GrowthController;

Route::get('/blog', [GrowthController::class, 'blogIndex'])->name('blog.index');
Route::get('/blog/{post}', [GrowthController::class, 'blogShow'])->name('blog.show');

Route::post('/locale', [GrowthController::class, 'switchLocale'])->name('locale.switch');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/report-abuse', [GrowthController::class, 'storeAbuse'])->name('abuse.store');
    Route::post('/push-subscriptions', [GrowthController::class, 'storePushSubscription'])->name('push.subscribe');

    Route::middleware('role:student')->group(function () {
        Route::get('/recommendations', [GrowthController::class, 'recommendations'])->name('recommendations.index');
        Route::post('/jobs/{job}/cover-letter-draft', [GrowthController::class, 'coverLetterDraft'])->name('jobs.cover-letter');
        Route::get('/portfolio', [GrowthController::class, 'portfolio'])->name('portfolio.index');
        Route::post('/portfolio', [GrowthController::class, 'storeProject'])->name('portfolio.store');
        Route::delete('/portfolio/{project}', [GrowthController::class, 'destroyProject'])->name('portfolio.destroy');
        Route::get('/resume/pdf', [GrowthController::class, 'resumePdf'])->name('resume.pdf');
        Route::get('/assessments', [GrowthController::class, 'assessments'])->name('assessments.index');
        Route::get('/assessments/{assessment}', [GrowthController::class, 'showAssessment'])->name('assessments.show');
        Route::post('/assessments/{assessment}', [GrowthController::class, 'submitAssessment'])->name('assessments.submit');
        Route::post('/companies/{company}/follow', [GrowthController::class, 'followCompany'])->name('companies.follow');
        Route::delete('/companies/{company}/follow', [GrowthController::class, 'unfollowCompany'])->name('companies.unfollow');
        Route::post('/companies/{company}/reviews', [GrowthController::class, 'storeReview'])->name('companies.reviews.store');
        Route::post('/offers/{offer}/respond', [GrowthController::class, 'respondOffer'])->name('offers.respond');
    });

    Route::get('/offers', [GrowthController::class, 'offers'])->name('offers.index');

    Route::middleware('role:recruiter,admin')->group(function () {
        Route::get('/team', [GrowthController::class, 'team'])->name('team.index');
        Route::post('/team', [GrowthController::class, 'inviteMember'])->name('team.invite');
        Route::get('/hiring-stages', [GrowthController::class, 'stages'])->name('stages.index');
        Route::post('/hiring-stages', [GrowthController::class, 'storeStage'])->name('stages.store');
        Route::post('/applications/bulk', [GrowthController::class, 'bulkApplications'])->name('applications.bulk');
        Route::post('/interviews/{interview}/scorecard', [GrowthController::class, 'storeScorecard'])->name('scorecards.store');
        Route::post('/offers', [GrowthController::class, 'storeOffer'])->name('offers.store');
        Route::get('/talent-pool', [GrowthController::class, 'talentPool'])->name('talent-pool');
        Route::post('/talent/blacklist', [GrowthController::class, 'blacklist'])->name('talent.blacklist');
    });

    Route::get('/interviews/{interview}/ics', [GrowthController::class, 'interviewIcs'])->name('interviews.ics');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/cms', [GrowthController::class, 'adminCms'])->name('cms');
        Route::post('/cms', [GrowthController::class, 'storePost'])->name('cms.store');
        Route::get('/analytics', [GrowthController::class, 'analytics'])->name('analytics');
        Route::get('/plans', [GrowthController::class, 'plans'])->name('plans');
        Route::patch('/plans/{company}', [GrowthController::class, 'updatePlan'])->name('plans.update');
        Route::get('/abuse-reports', [GrowthController::class, 'abuseIndex'])->name('abuse');
        Route::patch('/abuse-reports/{report}', [GrowthController::class, 'resolveAbuse'])->name('abuse.resolve');
        Route::get('/feature-flags', [GrowthController::class, 'flags'])->name('flags');
        Route::patch('/feature-flags/{flag}', [GrowthController::class, 'toggleFlag'])->name('flags.toggle');
        Route::get('/audit-export', [GrowthController::class, 'auditExport'])->name('audit.export');
    });
});
