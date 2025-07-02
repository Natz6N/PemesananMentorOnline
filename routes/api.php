<?php

use App\Http\Controllers\api\V1\Auth\AuthController;
use App\Http\Controllers\api\V1\BookingController;
use App\Http\Controllers\api\V1\CategoryController;
use App\Http\Controllers\api\V1\MentorAvailabilitieController;
use App\Http\Controllers\api\V1\MentorProfileController;
use App\Http\Controllers\api\V1\PaymentController;
use App\Http\Controllers\api\V1\ReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Struktur routing untuk aplikasi Booking Mentor dengan pembagian berdasarkan:
| 1. Public routes (tanpa autentikasi)
| 2. Protected routes (dengan autentikasi)
|    a. Admin routes (khusus admin)
|    b. Mentor routes (khusus mentor)
|    c. Student routes (khusus student)
|    d. Shared routes (semua role yang terautentikasi)
|
*/

// =================== PUBLIC ROUTES ===================
Route::middleware('throttle:auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Payment webhook (public route for payment gateway callbacks)
Route::post('/webhook/payment', [PaymentController::class, 'webhook'])
    ->middleware('throttle:payment');

// =================== PROTECTED ROUTES ===================
Route::middleware(['auth:sanctum', 'user.active', 'throttle:api'])->group(function () {
    // Auth profile routes
    Route::prefix('auth')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/update-profile', [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    });

    // =================== SHARED ROUTES ===================
    // Categories (public viewing)
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);

    // Mentors (public viewing)
    Route::get('/mentors', [MentorProfileController::class, 'index'])
        ->middleware('can:view-mentors');
    Route::get('/mentors/{id}', [MentorProfileController::class, 'show'])
        ->middleware('can:view-mentors');
    Route::get('/mentors/{id}/availability', [MentorAvailabilitieController::class, 'getMentorAvailability'])
        ->middleware('can:view-mentors');
    Route::get('/mentors/{id}/reviews', [ReviewController::class, 'mentorReviews'])
        ->middleware('can:view-mentors');

    // =================== STUDENT ROUTES ===================
    Route::middleware(['role:student'])->prefix('student')->group(function () {
        // Bookings
        Route::get('/bookings', [BookingController::class, 'index'])
            ->middleware('can:view-own-bookings');
        Route::post('/bookings', [BookingController::class, 'store'])
            ->middleware('can:create-booking');
        Route::get('/bookings/{booking}', [BookingController::class, 'show'])
            ->middleware('can:manage-own-booking');
        Route::put('/bookings/{booking}', [BookingController::class, 'update'])
            ->middleware('can:manage-own-booking');
        Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])
            ->middleware('can:manage-own-booking');

        // Reviews
        Route::post('/bookings/{booking}/reviews', [ReviewController::class, 'store'])
            ->middleware('can:create-review');
        Route::get('/reviews', [ReviewController::class, 'index']);
        Route::put('/reviews/{review}', [ReviewController::class, 'update']);
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

        // Payments
        Route::get('/payments', [PaymentController::class, 'index']);
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);
    });

    // =================== MENTOR ROUTES ===================
    Route::middleware(['role:mentor'])->prefix('mentor')->group(function () {
        // Profile Management
        Route::get('/profile', [MentorProfileController::class, 'getMentorOwnProfile'])
            ->middleware('can:manage-mentor-profile');
        Route::post('/profile', [MentorProfileController::class, 'store'])
            ->middleware('can:manage-mentor-profile');
        Route::put('/profile/{mentorProfile}', [MentorProfileController::class, 'update'])
            ->middleware('can:manage-mentor-profile');

        // Availability Management
        Route::get('/availabilities', [MentorAvailabilitieController::class, 'index'])
            ->middleware('can:manage-availability');
        Route::post('/availabilities', [MentorAvailabilitieController::class, 'store'])
            ->middleware('can:manage-availability');
        Route::put('/availabilities/{mentorAvailabilitie}', [MentorAvailabilitieController::class, 'update'])
            ->middleware('can:manage-availability');
        Route::delete('/availabilities/{mentorAvailabilitie}', [MentorAvailabilitieController::class, 'destroy'])
            ->middleware('can:manage-availability');
        Route::post('/availabilities/bulk', [MentorAvailabilitieController::class, 'setMentorAvailability'])
            ->middleware('can:manage-availability');

        // Booking Management
        Route::get('/bookings', [BookingController::class, 'index'])
            ->middleware('can:view-mentor-bookings');
        Route::get('/bookings/{booking}', [BookingController::class, 'show'])
            ->middleware('can:manage-mentor-booking');
        Route::put('/bookings/{booking}/confirm', [BookingController::class, 'confirmBooking'])
            ->middleware('can:manage-mentor-booking');
        Route::put('/bookings/{booking}/complete', [BookingController::class, 'completeBooking'])
            ->middleware('can:manage-mentor-booking');
        Route::put('/bookings/{booking}', [BookingController::class, 'update'])
            ->middleware('can:manage-mentor-booking');
    });

    // =================== ADMIN ROUTES ===================
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        // User Management
        Route::get('/users', [AuthController::class, 'getAllUsers'])
            ->middleware('can:manage-users');
        Route::get('/users/{user}', [AuthController::class, 'getUserById'])
            ->middleware('can:manage-users');
        Route::put('/users/{user}', [AuthController::class, 'updateUser'])
            ->middleware('can:manage-users');
        Route::delete('/users/{user}', [AuthController::class, 'deleteUser'])
            ->middleware('can:manage-users');

        // Category Management
        Route::post('/categories', [CategoryController::class, 'store'])
            ->middleware('can:manage-categories');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])
            ->middleware('can:manage-categories');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
            ->middleware('can:manage-categories');

        // Mentor Management
        Route::put('/mentors/{mentorProfile}', [MentorProfileController::class, 'update'])
            ->middleware('can:admin-access');
        Route::delete('/mentors/{mentorProfile}', [MentorProfileController::class, 'destroy'])
            ->middleware('can:admin-access');

        // Booking Management
        Route::get('/bookings', [BookingController::class, 'index'])
            ->middleware('can:admin-access');
        Route::get('/bookings/{booking}', [BookingController::class, 'show'])
            ->middleware('can:admin-access');
        Route::put('/bookings/{booking}', [BookingController::class, 'update'])
            ->middleware('can:admin-access');
        Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])
            ->middleware('can:admin-access');

        // Review Management
        Route::get('/reviews', [ReviewController::class, 'index'])
            ->middleware('can:moderate-reviews');
        Route::get('/reviews/{review}', [ReviewController::class, 'show'])
            ->middleware('can:moderate-reviews');
        Route::put('/reviews/{review}', [ReviewController::class, 'update'])
            ->middleware('can:moderate-reviews');
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])
            ->middleware('can:moderate-reviews');

        // Payment Management
        Route::get('/payments', [PaymentController::class, 'index'])
            ->middleware('can:admin-access');
        Route::get('/payments/{payment}', [PaymentController::class, 'show'])
            ->middleware('can:admin-access');
    });
});
