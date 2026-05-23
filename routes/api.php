<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/events',           [EventController::class, 'store']);
    Route::get('/analytics/summary', [AnalyticsController::class, 'summary']);
});
