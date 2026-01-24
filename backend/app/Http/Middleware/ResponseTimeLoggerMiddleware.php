<?php

namespace App\Http\Middleware;

use App\Support\FeatureFlags;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ResponseTimeLoggerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        $response = $next($request);

        if (FeatureFlags::enabled('response_time_logging')) {
            $durationMs = (microtime(true) - $start) * 1000;

            Log::info('response_time', [
                'method' => $request->getMethod(),
                'path' => $request->getPathInfo(),
                'status' => $response->getStatusCode(),
                'duration_ms' => round($durationMs, 2),
                'correlation_id' => $request->attributes->get('correlation_id'),
            ]);
        }

        return $response;
    }
}
