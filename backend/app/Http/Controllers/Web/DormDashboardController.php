<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class DormDashboardController extends Controller
{
    public function index()
    {
        $dormId = Auth::user()?->dormAdmin?->dorm_id;

        if (!$dormId) {
            abort(403);
        }

        $floorCount = Floor::where('dorm_id', $dormId)->count();
        $roomCount = Room::where('dorm_id', $dormId)->count();
        $studentCount = Student::where('dorm_id', $dormId)->count();

        return view('admin.dorm.dashboard', [
            'floorCount' => $floorCount,
            'roomCount' => $roomCount,
            'studentCount' => $studentCount,
        ]);
    }
}
