<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Endpoint public avec rate limiting
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/profiles', [ProfileController::class, 'getActiveProfiles']);
});

// Endpoints protégés par authentification avec rate limiting plus restrictif
Route::middleware(['auth:sanctum', 'throttle:30,1'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/profiles', [ProfileController::class, 'createProfile']);
    Route::match(['put', 'delete'], '/profiles/{profile}', [ProfileController::class, 'updateOrDeleteProfile']);
});
