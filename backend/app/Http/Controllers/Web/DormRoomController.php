<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignStudentRequest;
use App\Http\Requests\RoomRequest;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Student;
use App\Services\RoomAssignmentService;
use Illuminate\Http\Request;

class DormRoomController extends Controller
{
    protected function dormId(Request $request): int
    {
        $dormId = $request->user()?->dormAdmin?->dorm_id;

        if (!$dormId) {
            abort(403);
        }

        return $dormId;
    }

    public function index(Request $request)
    {
        $dormId = $this->dormId($request);
        $floors = Floor::where('dorm_id', $dormId)->orderBy('number')->get();

        $query = Room::where('dorm_id', $dormId)->with(['floor'])->withCount('activeAssignments');

        if ($request->filled('floor_id')) {
            $query->where('floor_id', $request->integer('floor_id'));
        }

        $rooms = $query->orderBy('room_number')->get();

        return view('admin.dorm.rooms.index', [
            'rooms' => $rooms,
            'floors' => $floors,
            'selectedFloor' => $request->integer('floor_id'),
        ]);
    }

    public function create(Request $request)
    {
        $dormId = $this->dormId($request);
        $floors = Floor::where('dorm_id', $dormId)->orderBy('number')->get();

        return view('admin.dorm.rooms.form', [
            'room' => new Room(),
            'floors' => $floors,
        ]);
    }

    public function store(RoomRequest $request)
    {
        $dormId = $this->dormId($request);
        $data = $request->validated();

        $floor = Floor::where('id', $data['floor_id'])
            ->where('dorm_id', $dormId)
            ->firstOrFail();

        Room::create([
            'dorm_id' => $dormId,
            'floor_id' => $floor->id,
            'room_number' => $data['room_number'],
            'capacity' => $data['capacity'] ?? 4,
            'status' => 'AVAILABLE',
        ]);

        return redirect()->route('admin.dorm.rooms.index')
            ->with('success', 'Room created successfully.');
    }

    public function show(Request $request, Room $room)
    {
        $dormId = $this->dormId($request);

        if ($room->dorm_id !== $dormId) {
            abort(403);
        }

        $room->load(['floor'])->loadCount('activeAssignments');

        $occupants = Student::whereHas('activeAssignment', function ($query) use ($room) {
            $query->where('room_id', $room->id)->where('active', true);
        })
            ->with('user')
            ->orderBy('full_name')
            ->get();

        $students = Student::where('dorm_id', $dormId)->orderBy('full_name')->get();

        return view('admin.dorm.rooms.show', [
            'room' => $room,
            'occupants' => $occupants,
            'students' => $students,
        ]);
    }

    public function edit(Request $request, Room $room)
    {
        $dormId = $this->dormId($request);

        if ($room->dorm_id !== $dormId) {
            abort(403);
        }

        $floors = Floor::where('dorm_id', $dormId)->orderBy('number')->get();

        return view('admin.dorm.rooms.form', [
            'room' => $room,
            'floors' => $floors,
        ]);
    }

    public function update(RoomRequest $request, Room $room, RoomAssignmentService $service)
    {
        $dormId = $this->dormId($request);

        if ($room->dorm_id !== $dormId) {
            abort(403);
        }

        $data = $request->validated();

        if (array_key_exists('floor_id', $data)) {
            $floor = Floor::where('id', $data['floor_id'])
                ->where('dorm_id', $dormId)
                ->firstOrFail();
            $room->floor_id = $floor->id;
        }

        if (array_key_exists('room_number', $data)) {
            $room->room_number = $data['room_number'];
        }

        if (array_key_exists('capacity', $data)) {
            $room->capacity = $data['capacity'];
        }

        $room->save();
        $service->updateRoomStatus($room->id);

        return redirect()->route('admin.dorm.rooms.index')
            ->with('success', 'Room updated successfully.');
    }

    public function destroy(Request $request, Room $room)
    {
        $dormId = $this->dormId($request);

        if ($room->dorm_id !== $dormId) {
            abort(403);
        }

        if ($room->activeAssignments()->exists()) {
            return redirect()->route('admin.dorm.rooms.index')
                ->with('error', 'Cannot delete a room with active assignments.');
        }

        $room->delete();

        return redirect()->route('admin.dorm.rooms.index')
            ->with('success', 'Room deleted successfully.');
    }

    public function assignStudent(AssignStudentRequest $request, Room $room, RoomAssignmentService $service)
    {
        $dormId = $this->dormId($request);

        if ($room->dorm_id !== $dormId) {
            abort(403);
        }

        $student = Student::where('id', $request->validated()['student_id'])
            ->where('dorm_id', $dormId)
            ->firstOrFail();

        $service->assignStudentToRoom($student, $room);

        return redirect()->route('admin.dorm.rooms.show', $room)
            ->with('success', 'Student assigned successfully.');
    }
}
