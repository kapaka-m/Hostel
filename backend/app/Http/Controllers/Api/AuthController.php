<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthLoginRequest;
use App\Http\Resources\UserResource;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\FeatureFlags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(AuthLoginRequest $request)
    {
        $logger = app(AuditLogger::class);

        if (!Auth::attempt($request->only('email', 'password'))) {
            $logger->log('auth.login_failed', null, null, [
                'email' => $request->input('email'),
            ]);

            if (FeatureFlags::enabled('audit_logs') && FeatureFlags::enabled('suspicious_login_alerts')) {
                $recentFailures = AuditLog::query()
                    ->where('action', 'auth.login_failed')
                    ->where('ip', $request->ip())
                    ->where('created_at', '>=', now()->subMinutes(15))
                    ->count();

                if ($recentFailures >= 5) {
                    $logger->log('auth.suspicious_login', null, null, [
                        'ip' => $request->ip(),
                        'count' => $recentFailures,
                    ]);
                }
            }

            return response()->json([
                'message' => 'Invalid credentials.',
                'errors' => [
                    'email' => ['Invalid credentials.'],
                ],
            ], 422);
        }

        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'errors' => [],
            ], 401);
        }

        if (FeatureFlags::enabled('user_freeze') && $user->isFrozen()) {
            Auth::logout();
            $logger->log('auth.login_blocked', $user, null, [
                'reason' => 'inactive',
            ]);

            return response()->json([
                'message' => 'Account is inactive.',
                'errors' => [],
            ], 403);
        }

        $token = $user->createToken('mobile')->plainTextToken;
        $logger->log('auth.login_success', $user, null, [
            'role' => $user->role,
        ]);

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        app(AuditLogger::class)->log('auth.logout', $request->user());

        return response()->json([
            'message' => 'Logged out successfully.',
            'errors' => [],
        ]);
    }
}
