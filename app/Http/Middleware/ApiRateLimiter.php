<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimiter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $limiterType = 'api'): Response
    {
        // Jika rate limiting dinonaktifkan, langsung lanjutkan request
        if (!config('rate_limiting.enabled', true)) {
            return $next($request);
        }

        // Ambil konfigurasi rate limiter dari config
        $config = config('rate_limiting.groups.' . $limiterType, [
            'max_attempts' => 60,
            'decay_minutes' => 1,
        ]);

        $maxAttempts = $config['max_attempts'];
        $decayMinutes = $config['decay_minutes'];

        // Tentukan identifier berdasarkan IP atau user ID jika terautentikasi
        $identifier = $request->user()
            ? $request->user()->id
            : $request->ip();

        // Format key sesuai konfigurasi
        $keyFormat = config('rate_limiting.key_format', '{group}:{identifier}');
        $key = str_replace(['{group}', '{identifier}'], [$limiterType, $identifier], $keyFormat);

        // Tambahkan rate limiter
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
                'code' => 429
            ], 429);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Tambahkan header rate limit info
        $headers = config('rate_limiting.headers', [
            'limit' => 'X-RateLimit-Limit',
            'remaining' => 'X-RateLimit-Remaining',
            'reset' => 'X-RateLimit-Reset',
        ]);

        $response->headers->add([
            $headers['limit'] => $maxAttempts,
            $headers['remaining'] => RateLimiter::remaining($key, $maxAttempts),
            $headers['reset'] => RateLimiter::availableIn($key),
        ]);

        return $response;
    }
}
