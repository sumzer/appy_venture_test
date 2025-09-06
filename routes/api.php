<?php

use App\Http\Controllers\BidController;
use App\Http\Controllers\LoadController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    // Loads
    Route::get('/loads', [LoadController::class, 'index']);
    Route::post('/loads', [LoadController::class, 'store']);
    Route::get('/loads/{load}', [LoadController::class, 'show']);
    Route::patch('/loads/{load}', [LoadController::class, 'update']);
    Route::delete('/loads/{load}', [LoadController::class, 'destroy']);

    // Bids
    Route::post('/loads/{load}/bids', [BidController::class, 'store']);
    Route::get('/loads/{load}/bids', [BidController::class, 'index']);
    Route::post('/bids/{bid}/accept', [BidController::class, 'accept']);
});
