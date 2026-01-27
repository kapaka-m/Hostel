<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminHomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user?->role === User::ROLE_SUPER_ADMIN) {
            return redirect()->route('admin.super.dashboard');
        }

        if ($user?->role === User::ROLE_UNIVERSITY_ADMIN) {
            return redirect()->route('admin.university.dashboard');
        }

        if ($user?->role === User::ROLE_DORM_ADMIN) {
            return redirect()->route('admin.dorm.dashboard');
        }

        abort(403);
    }
}
