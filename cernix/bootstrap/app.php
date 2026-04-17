<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\Exceptions\JWTException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // Suppress expected, non-actionable exceptions from Sentry.
        // Sentry\Laravel auto-registers itself; these additions prevent noise.
        $exceptions->dontReport([
            JWTException::class,          // expired / invalid / missing tokens → 401
            AuthenticationException::class,
            AuthorizationException::class,
            ValidationException::class,
        ]);

        // All JSON-expecting requests (API calls and AJAX from the UI) get a
        // consistent {status, message, data} envelope — never a raw stack trace.
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->expectsJson()) {
                return null; // let Laravel render the HTML error page normally
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Validation failed.',
                    'data'    => $e->errors(),
                ], 422);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unauthenticated.',
                    'data'    => null,
                ], 401);
            }

            if ($e instanceof AuthorizationException) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unauthorized.',
                    'data'    => null,
                ], 403);
            }

            $httpStatus = $e instanceof HttpException ? $e->getStatusCode() : 500;

            // Hide internal details in production
            $message = ($httpStatus === 500 && app()->environment('production'))
                ? 'An unexpected error occurred. Please try again.'
                : $e->getMessage();

            return response()->json([
                'status'  => 'error',
                'message' => $message,
                'data'    => null,
            ], $httpStatus);
        });

    })->create();
