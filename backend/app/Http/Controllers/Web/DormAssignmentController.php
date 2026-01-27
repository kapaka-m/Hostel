<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Services\RoomAssignmentService;
use Illuminate\Http\Request;

class DormAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeIfEnabled('viewAny', RoomAssignment::class);

        $dormId = $this->requireDormId($request);

        $query = RoomAssignment::whereHas('room', function ($builder) use ($dormId) {
            $builder->where('dorm_id', $dormId);
        })->with(['student.user', 'room.floor']);

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($builder) use ($term) {
                $builder->whereHas('student', function ($studentQuery) use ($term) {
                    $studentQuery->where('full_name', 'like', '%' . $term . '%')
                        ->orWhere('student_no', 'like', '%' . $term . '%');
                })->orWhereHas('room', function ($roomQuery) use ($term) {
                    $roomQuery->where('room_number', 'like', '%' . $term . '%');
                });
            });
        }

        if ($request->filled('status')) {
            $query->where('active', $request->input('status') === 'active');
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->integer('room_id'));
        }

        $assignments = $query->orderByDesc('from_date')
            ->paginate(20)
            ->withQueryString();

        $rooms = Room::where('dorm_id', $dormId)->orderBy('room_number')->get();

        return view('admin.dorm.assignments.index', [
            'assignments' => $assignments,
            'rooms' => $rooms,
            'filters' => [
                'q' => $request->input('q', ''),
                'status' => $request->input('status', ''),
                'room_id' => $request->input('room_id', ''),
            ],
        ]);
    }

    public function edit(Request $request, RoomAssignment $assignment)
    {
        $dormId = $this->requireDormId($request);

        $assignment->load(['student', 'room.floor']);

        if ($assignment->room?->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('update', $assignment);

        $rooms = Room::where('dorm_id', $dormId)->orderBy('room_number')->get();

        return view('admin.dorm.assignments.move', [
            'assignment' => $assignment,
            'rooms' => $rooms,
        ]);
    }

    public function update(Request $request, RoomAssignment $assignment, RoomAssignmentService $service)
    {
        $dormId = $this->requireDormId($request);

        $assignment->load(['student', 'room']);

        if ($assignment->room?->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('update', $assignment);

        $data = $request->validate([
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
        ]);

        $room = Room::where('id', $data['room_id'])
            ->where('dorm_id', $dormId)
            ->firstOrFail();

        $service->assignStudentToRoom($assignment->student, $room);

        return redirect()->route('admin.dorm.assignments.index')
            ->with('success', 'Assignment moved successfully.');
    }
}
