<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Application\JobApplicationController;

Route::middleware(['auth:api'])->prefix('applications')->group(function () {
    Route::get('/', [JobApplicationController::class, 'index']);
    Route::post('/', [JobApplicationController::class, 'store']);
    Route::get('/{application}', [JobApplicationController::class, 'show']);
    Route::patch('/{application}/status', [JobApplicationController::class, 'updateStatus']); // for admin or company
    Route::post('/{application}/withdraw', [JobApplicationController::class, 'withdraw']); // for candidate
});
