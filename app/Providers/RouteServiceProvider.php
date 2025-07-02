<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // API rate limiter - 60 requests per minute per user/IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(config('rate_limiting.groups.api.max_attempts', 60))
                ->by($request->user()?->id ?: $request->ip());
        });

        // Auth rate limiter - 5 requests per minute per IP
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(config('rate_limiting.groups.auth.max_attempts', 5))
                ->by($request->ip());
        });

        // Payment rate limiter - 10 requests per minute per IP
        RateLimiter::for('payment', function (Request $request) {
            return Limit::perMinute(config('rate_limiting.groups.payment.max_attempts', 10))
                ->by($request->ip());
        });
    }
}
