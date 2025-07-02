<?php

use App\Http\Controllers\api\V1\Auth\AuthController;
use Illuminate\Support\Facades\Route;
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/update-profile', [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/user', [AuthController::class, '']);
    });
    Route::middleware('role:mentor')->group(function () {

    });
    Route::middleware('role:student')->group(function () {

    });
});
