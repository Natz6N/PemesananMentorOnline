<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class BroadcastAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika sudah terotentikasi, langsung lanjutkan
        if (Auth::check()) {
            return $next($request);
        }

        // Ambil token dari request
        $token = $request->bearerToken() ?? $request->query('token');

        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Validasi token menggunakan Sanctum
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken || !$accessToken->tokenable) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        // Login user berdasarkan token
        Auth::login($accessToken->tokenable);

        return $next($request);
    }
}
