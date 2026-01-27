<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Dorm;
use App\Models\Room;
use App\Models\Student;
use Illuminate\Http\Request;

class UniversityDashboardController extends Controller
{
    public function index(Request $request)
    {
        $universityId = $this->requireUniversityId($request);

        $dormCount = Dorm::where('university_id', $universityId)->count();
        $roomCount = Room::whereHas('dorm', function ($query) use ($universityId) {
            $query->where('university_id', $universityId);
        })->count();
        $studentCount = Student::whereHas('dorm', function ($query) use ($universityId) {
            $query->where('university_id', $universityId);
        })->count();

        return view('admin.university.dashboard', [
            'dormCount' => $dormCount,
            'roomCount' => $roomCount,
            'studentCount' => $studentCount,
        ]);
    }
}
