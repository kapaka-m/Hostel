<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\WebLoginRequest;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\FeatureFlags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(WebLoginRequest $request)
    {
        $credentials = $request->validated();

        $logger = app(AuditLogger::class);

        if (!Auth::attempt($credentials)) {
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

            return back()->withErrors([
                'email' => 'Invalid credentials.',
            ])->withInput();
        }

        $request->session()->regenerate();

        $user = $request->user();

        if ($user && FeatureFlags::enabled('user_freeze') && $user->isFrozen()) {
            Auth::logout();
            $logger->log('auth.login_blocked', $user, null, [
                'reason' => 'inactive',
            ]);

            return back()->withErrors([
                'email' => 'Account is inactive.',
            ])->withInput();
        }

        if ($user) {
            $logger->log('auth.login_success', $user, null, [
                'role' => $user->role,
            ]);
        }

        if ($user->role === User::ROLE_SUPER_ADMIN) {
            return redirect()->route('admin.home');
        }

        if ($user->role === User::ROLE_UNIVERSITY_ADMIN) {
            return redirect()->route('admin.university.dashboard');
        }

        if ($user->role === User::ROLE_DORM_ADMIN) {
            return redirect()->route('admin.dorm.dashboard');
        }

        Auth::logout();

        return back()->withErrors([
            'email' => 'Students cannot access the admin panel.',
        ]);
    }

    public function logout(Request $request)
    {
        app(AuditLogger::class)->log('auth.logout', $request->user());

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
