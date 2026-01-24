<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Dorm;
use App\Models\Room;
use App\Models\Student;

class UniversityDashboardController extends Controller
{
    public function index()
    {
        $dormCount = Dorm::count();
        $roomCount = Room::count();
        $studentCount = Student::count();

        return view('admin.university.dashboard', [
            'dormCount' => $dormCount,
            'roomCount' => $roomCount,
            'studentCount' => $studentCount,
        ]);
    }
}
