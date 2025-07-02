<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Services\FileStorageService;
use App\Models\User;
use App\Models\Booking;
use App\Models\MentorProfile;
use App\Models\MentorAvailabilitie;
use App\Models\Review;
use App\Models\Payment;
use App\Models\Category;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FileStorageService::class, function ($app) {
            return new FileStorageService('public'); // atau disk lain sesuai kebutuhan
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Middleware pengecekan status user
        $isActive = fn (User $user) => $user->status === 'active';

        // ===== Gate untuk Student =====

        // Melihat mentor
        Gate::define('view-mentors', function (User $user) use ($isActive) {
            return $isActive($user);
        });

        // Membuat booking
        Gate::define('create-booking', function (User $user) use ($isActive) {
            return $user->isStudent() && $isActive($user);
        });

        // Melihat booking milik sendiri
        Gate::define('view-own-bookings', function (User $user) use ($isActive) {
            return $user->isStudent() && $isActive($user);
        });

        // Mengelola booking milik sendiri
        Gate::define('manage-own-booking', function (User $user, Booking $booking) use ($isActive) {
            return $user->isStudent() && $isActive($user) && $user->id === $booking->student_id;
        });

        // Membuat review
        Gate::define('create-review', function (User $user, Booking $booking) use ($isActive) {
            return $user->isStudent() && $isActive($user) &&
                   $user->id === $booking->student_id &&
                   $booking->status === 'completed';
        });

        // ===== Gate untuk Mentor =====

        // Mengelola profil mentor
        Gate::define('manage-mentor-profile', function (User $user, MentorProfile $profile = null) use ($isActive) {
            if (!$user->isMentor() || !$isActive($user)) {
                return false;
            }

            if ($profile) {
                return $user->id === $profile->user_id;
            }

            return true;
        });

        // Mengelola ketersediaan waktu mentor
        Gate::define('manage-availability', function (User $user, MentorAvailabilitie $availability = null) use ($isActive) {
            if (!$user->isMentor() || !$isActive($user)) {
                return false;
            }

            if ($availability) {
                $mentorProfile = $availability->mentorProfile;
                return $user->id === $mentorProfile->user_id;
            }

            return true;
        });

        // Melihat booking yang masuk
        Gate::define('view-mentor-bookings', function (User $user) use ($isActive) {
            return $user->isMentor() && $isActive($user);
        });

        // Mengelola booking sebagai mentor
        Gate::define('manage-mentor-booking', function (User $user, Booking $booking) use ($isActive) {
            return $user->isMentor() && $isActive($user) && $user->id === $booking->mentor_id;
        });

        // ===== Gate untuk Admin =====

        // Akses penuh ke semua data
        Gate::define('admin-access', function (User $user) use ($isActive) {
            return $user->role === 'admin' && $isActive($user);
        });

        // Moderasi review
        Gate::define('moderate-reviews', function (User $user) use ($isActive) {
            return $user->role === 'admin' && $isActive($user);
        });

        // Mengelola kategori
        Gate::define('manage-categories', function (User $user) use ($isActive) {
            return $user->role === 'admin' && $isActive($user);
        });

        // Mengelola user
        Gate::define('manage-users', function (User $user) use ($isActive) {
            return $user->role === 'admin' && $isActive($user);
        });
    }
}
