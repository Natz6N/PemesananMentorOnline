<?php

use App\Http\Controllers\api\V1\Auth\AuthController;
use App\Http\Controllers\api\V1\BookingController;
use App\Http\Controllers\api\V1\CategoryController;
use App\Http\Controllers\api\V1\MentorAvailabilitieController;
use App\Http\Controllers\api\V1\MentorCategoryController;
use App\Http\Controllers\api\V1\MentorProfileController;
use App\Http\Controllers\api\V1\PaymentController;
use App\Http\Controllers\api\V1\ReviewController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth profile routes
    Route::prefix('auth')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/update-profile', [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    });

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Mentors
    Route::apiResource('mentors', MentorProfileController::class);
    Route::get('/mentors/{id}/availability', [MentorAvailabilitieController::class, 'getMentorAvailability']);
    Route::post('/mentors/{id}/availability', [MentorAvailabilitieController::class, 'setMentorAvailability']);
    Route::get('/mentors/{id}/reviews', [ReviewController::class, 'mentorReviews']);

    // Bookings
    Route::apiResource('bookings', BookingController::class);
    Route::put('/bookings/{id}/confirm', [BookingController::class, 'confirmBooking']);
    Route::put('/bookings/{id}/complete', [BookingController::class, 'completeBooking']);

    // Payments
    Route::apiResource('payments', PaymentController::class)->except(['update', 'destroy']);
    Route::post('/payments/webhook', [PaymentController::class, 'webhook']);

    // Reviews
    Route::apiResource('reviews', ReviewController::class);

    // Admin routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [AuthController::class, 'getAllUsers']);
    });

    // Mentor routes
    Route::middleware('role:mentor')->group(function () {
        // Mentor-specific endpoints
    });

    // Student routes
    Route::middleware('role:student')->group(function () {
        // Student-specific endpoints
    });
});

// Payment webhook (public route for payment gateway callbacks)
Route::post('/webhook/payment', [PaymentController::class, 'webhook']);
