<?php

use App\Http\Controllers\Ai\AiMentorController;
use App\Http\Controllers\Ai\JobMatchController;
use App\Http\Controllers\Cv\CvController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix('ai')->group(function () {

    Route::post('/cv/upload', [CvController::class, 'store']);

    Route::get('/jobs/{job}/match-report', [JobMatchController::class, 'getMatchReport']);

    Route::post('/mentor/ask', [AiMentorController::class, 'ask']);

});
