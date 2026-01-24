<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'feature' => \App\Http\Middleware\FeatureFlagMiddleware::class,
            'active' => \App\Http\Middleware\EnsureUserActive::class,
            'admin.ip' => \App\Http\Middleware\AdminIpAllowlist::class,
        ]);

        $middleware->appendToGroup('api', [
            \App\Http\Middleware\CorrelationIdMiddleware::class,
            \App\Http\Middleware\ResponseTimeLoggerMiddleware::class,
            \App\Http\Middleware\EnsureUserActive::class,
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\CorrelationIdMiddleware::class,
            \App\Http\Middleware\ResponseTimeLoggerMiddleware::class,
            \App\Http\Middleware\EnsureUserActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ModelNotFoundException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Resource not found.',
                    'errors' => [],
                ], 404);
            }
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if ($request->expectsJson()) {
                $status = $exception->getStatusCode();
                $message = $exception->getMessage() ?: 'Request failed.';

                return response()->json([
                    'message' => $message,
                    'errors' => [],
                ], $status);
            }
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (!$request->expectsJson()) {
                return null;
            }

            if ($exception instanceof ValidationException) {
                return null;
            }

            return response()->json([
                'message' => 'Server error.',
                'errors' => [],
            ], 500);
        });
    })->create();
