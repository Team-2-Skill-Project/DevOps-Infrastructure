<?php

use App\Http\Controllers\Cv\CvController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->prefix('cv')->group(function () {
    Route::post('/upload', [CvController::class, 'store']);
    Route::get('/history', [CvController::class, 'history']);
    Route::get('/status/{cvDocument}', [CvController::class, 'show']);
    Route::post('/retry/{cvDocument}', [CvController::class, 'retry']);
    Route::post('/extractions/{extraction}/verify', [CvController::class, 'verify']);
});
