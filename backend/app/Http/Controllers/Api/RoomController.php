<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignStudentRequest;
use App\Http\Requests\RoomRequest;
use App\Http\Resources\RoomResource;
use App\Http\Resources\StudentResource;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Student;
use App\Services\RoomAssignmentService;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeIfEnabled('viewAny', Room::class);

        $dormId = $this->requireDormId($request);

        $query = Room::where('dorm_id', $dormId)->withCount('activeAssignments');

        if ($request->filled('floor_id')) {
            $query->where('floor_id', $request->integer('floor_id'));
        }

        $rooms = $query->orderBy('room_number')->get();

        return RoomResource::collection($rooms);
    }

    public function store(RoomRequest $request)
    {
        $dormId = $this->requireDormId($request);
        $this->authorizeIfEnabled('create', [Room::class, $dormId]);

        $data = $request->validated();

        $floor = Floor::where('id', $data['floor_id'])
            ->where('dorm_id', $dormId)
            ->firstOrFail();

        $room = Room::create([
            'dorm_id' => $dormId,
            'floor_id' => $floor->id,
            'room_number' => $data['room_number'],
            'capacity' => $data['capacity'] ?? 4,
            'status' => 'AVAILABLE',
        ]);

        $room->loadCount('activeAssignments');

        return new RoomResource($room);
    }

    public function show(Request $request, Room $room)
    {
        $dormId = $this->requireDormId($request);

        if ($room->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('view', $room);

        $room->loadCount('activeAssignments');

        return new RoomResource($room);
    }

    public function update(RoomRequest $request, Room $room, RoomAssignmentService $service)
    {
        $dormId = $this->requireDormId($request);

        if ($room->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('update', $room);

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

        $room->loadCount('activeAssignments');

        return new RoomResource($room);
    }

    public function destroy(Request $request, Room $room)
    {
        $dormId = $this->requireDormId($request);

        if ($room->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('delete', $room);

        if ($room->activeAssignments()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a room with active assignments.',
                'errors' => [],
            ], 422);
        }

        $room->delete();

        return response()->json([
            'message' => 'Room deleted successfully.',
            'errors' => [],
        ]);
    }

    public function assignStudent(AssignStudentRequest $request, Room $room, RoomAssignmentService $service)
    {
        $dormId = $this->requireDormId($request);

        if ($room->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('assignStudent', $room);

        $student = Student::where('id', $request->validated()['student_id'])
            ->where('dorm_id', $dormId)
            ->firstOrFail();

        $assignment = $service->assignStudentToRoom($student, $room);

        return response()->json([
            'message' => 'Student assigned successfully.',
            'errors' => [],
            'assignment_id' => $assignment->id,
        ]);
    }

    public function occupants(Request $request, Room $room)
    {
        $dormId = $this->requireDormId($request);

        if ($room->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('viewOccupants', $room);

        $students = Student::whereHas('activeAssignment', function ($query) use ($room) {
            $query->where('room_id', $room->id)->where('active', true);
        })
            ->with('user')
            ->orderBy('full_name')
            ->get();

        return StudentResource::collection($students);
    }
}
