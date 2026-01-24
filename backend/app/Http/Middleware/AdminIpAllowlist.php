<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use App\Support\FeatureFlags;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminIpAllowlist
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!FeatureFlags::enabled('admin_ip_allowlist')) {
            return $next($request);
        }

        $allowed = SystemSetting::getValue('security.admin_ip_allowlist', []);

        if (!is_array($allowed) || $allowed === []) {
            return $next($request);
        }

        if (!in_array($request->ip(), $allowed, true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'IP not allowed.',
                    'errors' => [],
                ], 403);
            }

            abort(403, 'IP not allowed.');
        }

        return $next($request);
    }
}
