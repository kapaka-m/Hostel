<?php

namespace App\Http\Middleware;

use App\Support\FeatureFlags;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CorrelationIdMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!FeatureFlags::enabled('correlation_ids')) {
            return $next($request);
        }

        $correlationId = $request->headers->get('X-Correlation-Id');

        if (!$correlationId) {
            $correlationId = (string) Str::uuid();
            $request->headers->set('X-Correlation-Id', $correlationId);
        }

        $request->attributes->set('correlation_id', $correlationId);

        $response = $next($request);
        $response->headers->set('X-Correlation-Id', $correlationId);

        return $response;
    }
}
