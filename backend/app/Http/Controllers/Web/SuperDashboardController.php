<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Dorm;
use App\Models\University;
use App\Models\User;

class SuperDashboardController extends Controller
{
    public function index()
    {
        return view('admin.super.dashboard', [
            'universityCount' => University::count(),
            'dormCount' => Dorm::count(),
            'userCount' => User::count(),
        ]);
    }
}
