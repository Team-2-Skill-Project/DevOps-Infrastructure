<?php

use App\Http\Controllers\Application\JobApplicationController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', SetLocale::class])->prefix('applications')->group(function () {
    Route::get('/', [JobApplicationController::class, 'index']);
    Route::post('/', [JobApplicationController::class, 'store']);
    Route::get('/{application}', [JobApplicationController::class, 'show']);
    Route::patch('/{application}/status', [JobApplicationController::class, 'updateStatus']); // for admin or company
    Route::post('/{application}/withdraw', [JobApplicationController::class, 'withdraw']); // for candidate
});
