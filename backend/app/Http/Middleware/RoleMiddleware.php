<?php

namespace App\Http\Middleware;

use App\Support\FeatureFlags;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'errors' => [],
                ], 401);
            }

            return redirect()->route('login');
        }

        $hasSpatieRoles = FeatureFlags::enabled('permissions')
            && method_exists($user, 'roles')
            && $user->roles()->exists();

        if ($hasSpatieRoles) {
            if (!$user->hasAnyRole($roles)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Forbidden.',
                        'errors' => [],
                    ], 403);
                }

                abort(403);
            }

            return $next($request);
        }

        if (!in_array($user->role, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden.',
                    'errors' => [],
                ], 403);
            }

            abort(403);
        }

        return $next($request);
    }
}
