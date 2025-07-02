<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->status !== 'active') {
            return response()->json([
                'message' => 'Akun anda tidak aktif. Silahkan hubungi administrator.',
                'code' => 403
            ], 403);
        }

        return $next($request);
    }
}
