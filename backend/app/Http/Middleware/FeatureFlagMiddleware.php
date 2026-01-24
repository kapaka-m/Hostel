<?php

namespace App\Http\Middleware;

use App\Support\FeatureFlags;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FeatureFlagMiddleware
{
    public function handle(Request $request, Closure $next, string $flag): Response
    {
        if (!FeatureFlags::enabled($flag)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Feature not enabled.',
                    'errors' => [],
                ], 404);
            }

            abort(404);
        }

        return $next($request);
    }
}
