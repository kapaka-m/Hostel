<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'Invalid credentials.',
            ])->withInput();
        }

        $request->session()->regenerate();

        $user = $request->user();

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
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
