<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Services\FileStorageService;
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
        // Gate::define('delete-booking', function ($user, Booking $booking) {
        //     return $user->id === $booking->user_id;
        // });
    }
}
