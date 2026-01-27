<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Dorm;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\Student;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UniversityReportController extends Controller
{
    public function index(Request $request)
    {
        $universityId = $this->requireUniversityId($request);

        $totalDorms = Dorm::where('university_id', $universityId)->count();
        $totalStudents = Student::whereHas('dorm', function ($query) use ($universityId) {
            $query->where('university_id', $universityId);
        })->count();
        $totalRooms = Room::whereHas('dorm', function ($query) use ($universityId) {
            $query->where('university_id', $universityId);
        })->count();

        $capacityByDorm = Room::selectRaw('dorms.id as dorm_id, SUM(rooms.capacity) as capacity')
            ->join('dorms', 'rooms.dorm_id', '=', 'dorms.id')
            ->where('dorms.university_id', $universityId)
            ->groupBy('dorms.id')
            ->pluck('capacity', 'dorm_id');

        $occupancyByDorm = RoomAssignment::selectRaw('dorms.id as dorm_id, COUNT(room_assignments.id) as count')
            ->join('rooms', 'room_assignments.room_id', '=', 'rooms.id')
            ->join('dorms', 'rooms.dorm_id', '=', 'dorms.id')
            ->where('dorms.university_id', $universityId)
            ->where('room_assignments.active', true)
            ->groupBy('dorms.id')
            ->pluck('count', 'dorm_id');

        $totalCapacity = (int) $capacityByDorm->sum();
        $totalOccupancy = (int) $occupancyByDorm->sum();

        $dorms = Dorm::where('university_id', $universityId)
            ->orderBy('name')
            ->get()
            ->map(function ($dorm) use ($capacityByDorm, $occupancyByDorm) {
                $capacity = (int) ($capacityByDorm[$dorm->id] ?? 0);
                $occupied = (int) ($occupancyByDorm[$dorm->id] ?? 0);
                $percent = $capacity > 0 ? (int) round(($occupied / $capacity) * 100) : 0;

                return [
                    'id' => $dorm->id,
                    'name' => $dorm->name,
                    'capacity' => $capacity,
                    'occupied' => $occupied,
                    'percent' => $percent,
                ];
            });

        $startDate = Carbon::now()->subDays(13)->startOfDay();
        $assignments = RoomAssignment::selectRaw('DATE(room_assignments.from_date) as day, COUNT(room_assignments.id) as count')
            ->join('rooms', 'room_assignments.room_id', '=', 'rooms.id')
            ->join('dorms', 'rooms.dorm_id', '=', 'dorms.id')
            ->where('dorms.university_id', $universityId)
            ->where('room_assignments.from_date', '>=', $startDate)
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('count', 'day');

        $trendLabels = [];
        $trendValues = [];
        for ($i = 0; $i < 14; $i++) {
            $day = $startDate->copy()->addDays($i)->format('Y-m-d');
            $trendLabels[] = $day;
            $trendValues[] = (int) ($assignments[$day] ?? 0);
        }

        $ticketStatusCounts = Ticket::where('university_id', $universityId)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('admin.university.reports.index', [
            'totals' => [
                'dorms' => $totalDorms,
                'students' => $totalStudents,
                'rooms' => $totalRooms,
                'capacity' => $totalCapacity,
                'occupied' => $totalOccupancy,
            ],
            'dorms' => $dorms,
            'trend' => [
                'labels' => $trendLabels,
                'values' => $trendValues,
            ],
            'ticketStatusCounts' => $ticketStatusCounts,
        ]);
    }
}
