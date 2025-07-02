<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'user.active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        ]);

        // Add api group middlewares
        $middleware->group('api', [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Add web group middlewares
        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Register global middleware
        $middleware->append([
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Register custom exception handler for API responses
        $exceptions->renderable(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return handleApiException($e, $request);
            }
            return null;
        });
    })->create();

/**
 * Tangani pengecualian API untuk format JSON yang konsisten
 */
function handleApiException(\Throwable $e, \Illuminate\Http\Request $request)
{
    // Handle 404 Not Found
    if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ||
        $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
        return response()->json([
            'message' => 'Resource tidak ditemukan.',
            'code' => 404
        ], 404);
    }

    // Handle 401 Unauthenticated
    if ($e instanceof \Illuminate\Auth\AuthenticationException) {
        return response()->json([
            'message' => 'Unauthenticated.',
            'code' => 401
        ], 401);
    }

    // Handle 403 Unauthorized
    if ($e instanceof \Illuminate\Auth\Access\AuthorizationException ||
        $e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
        return response()->json([
            'message' => 'Unauthorized.',
            'code' => 403
        ], 403);
    }

    // Handle 422 Validation errors
    if ($e instanceof \Illuminate\Validation\ValidationException) {
        return response()->json([
            'message' => 'Data yang diberikan tidak valid.',
            'errors' => $e->validator->errors()->toArray(),
            'code' => 422
        ], 422);
    }

    // Handle 429 Too Many Requests
    if ($e instanceof \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException) {
        return response()->json([
            'message' => 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
            'code' => 429
        ], 429);
    }

    // Handle semua pengecualian lainnya (500 Internal Server Error)
    $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
    $message = $e->getMessage();

    if (config('app.debug')) {
        return response()->json([
            'message' => $message ?: 'Terjadi kesalahan server.',
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace(),
            'code' => $statusCode
        ], $statusCode);
    }

    return response()->json([
        'message' => 'Terjadi kesalahan server.',
        'code' => $statusCode
    ], $statusCode);
}
