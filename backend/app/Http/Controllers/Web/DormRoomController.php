<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignStudentRequest;
use App\Http\Requests\RoomRequest;
use App\Models\Floor;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\Student;
use App\Services\RoomAssignmentService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DormRoomController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeIfEnabled('viewAny', Room::class);

        $dormId = $this->requireDormId($request);
        $floors = Floor::where('dorm_id', $dormId)->orderBy('number')->get();

        $query = Room::where('dorm_id', $dormId)->with(['floor'])->withCount('activeAssignments');

        if ($request->filled('visibility') && $request->input('visibility') === 'archived') {
            $query->onlyTrashed();
        }

        if ($request->filled('q')) {
            $query->where('room_number', 'like', '%' . $request->input('q') . '%');
        }

        if ($request->filled('floor_id')) {
            $query->where('floor_id', $request->integer('floor_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } elseif ($request->filled('availability')) {
            if ($request->input('availability') === 'has_space') {
                $query->whereIn('status', ['AVAILABLE', 'PARTIAL']);
            } elseif ($request->input('availability') === 'full') {
                $query->where('status', 'FULL');
            }
        }

        $rooms = $query->orderBy('room_number')->paginate(24)->withQueryString();

        return view('admin.dorm.rooms.index', [
            'rooms' => $rooms,
            'floors' => $floors,
            'filters' => [
                'q' => $request->input('q', ''),
                'floor_id' => $request->input('floor_id', ''),
                'status' => $request->input('status', ''),
                'availability' => $request->input('availability', ''),
                'visibility' => $request->input('visibility', ''),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $dormId = $this->requireDormId($request);

        $this->authorizeIfEnabled('create', [Room::class, $dormId]);

        $floors = Floor::where('dorm_id', $dormId)->orderBy('number')->get();

        return view('admin.dorm.rooms.form', [
            'room' => new Room,
            'floors' => $floors,
        ]);
    }

    public function store(RoomRequest $request)
    {
        $dormId = $this->requireDormId($request);
        $this->authorizeIfEnabled('create', [Room::class, $dormId]);

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
        $dormId = $this->requireDormId($request);

        if ($room->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('view', $room);

        $room->load(['floor'])->loadCount('activeAssignments');

        $assignments = RoomAssignment::where('room_id', $room->id)
            ->where('active', true)
            ->with(['student.user'])
            ->orderByDesc('from_date')
            ->get();

        $students = Student::where('dorm_id', $dormId)
            ->with(['activeAssignment.room'])
            ->orderBy('full_name')
            ->get();

        return view('admin.dorm.rooms.show', [
            'room' => $room,
            'assignments' => $assignments,
            'students' => $students,
        ]);
    }

    public function edit(Request $request, Room $room)
    {
        $dormId = $this->requireDormId($request);

        if ($room->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('update', $room);

        $floors = Floor::where('dorm_id', $dormId)->orderBy('number')->get();

        return view('admin.dorm.rooms.form', [
            'room' => $room,
            'floors' => $floors,
        ]);
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

        return redirect()->route('admin.dorm.rooms.index')
            ->with('success', 'Room updated successfully.');
    }

    public function destroy(Request $request, Room $room)
    {
        $dormId = $this->requireDormId($request);

        if ($room->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('delete', $room);

        if ($room->activeAssignments()->exists()) {
            return redirect()->route('admin.dorm.rooms.index')
                ->with('error', 'Cannot delete a room with active assignments.');
        }

        $room->delete();

        return redirect()->route('admin.dorm.rooms.index')
            ->with('success', 'Room archived successfully.');
    }

    public function restore(Request $request, Room $room)
    {
        $dormId = $this->requireDormId($request);

        if ($room->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('update', $room);

        $room->restore();

        return redirect()->route('admin.dorm.rooms.index', [
            'visibility' => 'archived',
        ])->with('success', 'Room restored successfully.');
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizeIfEnabled('viewAny', Room::class);

        $dormId = $this->requireDormId($request);
        $query = Room::where('dorm_id', $dormId)->with(['floor'])->withCount('activeAssignments');

        if ($request->filled('visibility') && $request->input('visibility') === 'archived') {
            $query->onlyTrashed();
        }

        if ($request->filled('q')) {
            $query->where('room_number', 'like', '%' . $request->input('q') . '%');
        }

        if ($request->filled('floor_id')) {
            $query->where('floor_id', $request->integer('floor_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } elseif ($request->filled('availability')) {
            if ($request->input('availability') === 'has_space') {
                $query->whereIn('status', ['AVAILABLE', 'PARTIAL']);
            } elseif ($request->input('availability') === 'full') {
                $query->where('status', 'FULL');
            }
        }

        $filename = 'rooms_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['room_number', 'floor', 'capacity', 'status', 'occupancy']);

            $query->orderBy('room_number')->chunk(200, function ($rooms) use ($handle) {
                foreach ($rooms as $room) {
                    fputcsv($handle, [
                        $room->room_number,
                        $room->floor?->number,
                        $room->capacity,
                        $room->status,
                        $room->active_assignments_count,
                    ]);
                }
            });

            fclose($handle);
        }, $filename);
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

        $service->assignStudentToRoom($student, $room);

        return redirect()->route('admin.dorm.rooms.show', $room)
            ->with('success', 'Student assigned successfully.');
    }

    public function unassignStudent(Request $request, Room $room, RoomAssignmentService $service)
    {
        $dormId = $this->requireDormId($request);

        if ($room->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('assignStudent', $room);

        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
        ]);

        $student = Student::where('id', $data['student_id'])
            ->where('dorm_id', $dormId)
            ->firstOrFail();

        $assignment = $student->activeAssignment()
            ->where('room_id', $room->id)
            ->first();

        if (!$assignment) {
            return redirect()->route('admin.dorm.rooms.show', $room)
                ->with('error', 'Student is not assigned to this room.');
        }

        $service->deactivateStudentAssignment($student);

        return redirect()->route('admin.dorm.rooms.show', $room)
            ->with('success', 'Student unassigned successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $this->authorizeIfEnabled('viewAny', Room::class);

        $dormId = $this->requireDormId($request);
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:rooms,id'],
        ]);

        $rooms = Room::where('dorm_id', $dormId)
            ->whereIn('id', $data['ids'])
            ->get();

        if ($rooms->isEmpty()) {
            return redirect()->route('admin.dorm.rooms.index')
                ->with('error', 'No rooms selected.');
        }

        $blocked = $rooms->filter(fn($room) => $room->activeAssignments()->exists());

        if ($blocked->isNotEmpty()) {
            return redirect()->route('admin.dorm.rooms.index')
                ->with('error', 'Some rooms have active assignments and cannot be archived.');
        }

        foreach ($rooms as $room) {
            $this->authorizeIfEnabled('delete', $room);
        }

        Room::where('dorm_id', $dormId)
            ->whereIn('id', $rooms->pluck('id'))
            ->delete();

        return redirect()->route('admin.dorm.rooms.index')
            ->with('success', 'Selected rooms archived successfully.');
    }

    public function bulkRestore(Request $request)
    {
        $this->authorizeIfEnabled('viewAny', Room::class);

        $dormId = $this->requireDormId($request);
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:rooms,id'],
        ]);

        $rooms = Room::onlyTrashed()
            ->where('dorm_id', $dormId)
            ->whereIn('id', $data['ids'])
            ->get();

        if ($rooms->isEmpty()) {
            return redirect()->route('admin.dorm.rooms.index', ['visibility' => 'archived'])
                ->with('error', 'No archived rooms selected.');
        }

        foreach ($rooms as $room) {
            $this->authorizeIfEnabled('update', $room);
        }

        Room::onlyTrashed()
            ->where('dorm_id', $dormId)
            ->whereIn('id', $rooms->pluck('id'))
            ->restore();

        return redirect()->route('admin.dorm.rooms.index', ['visibility' => 'archived'])
            ->with('success', 'Selected rooms restored successfully.');
    }

    public function bulkExport(Request $request): StreamedResponse
    {
        $this->authorizeIfEnabled('viewAny', Room::class);

        $dormId = $this->requireDormId($request);
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:rooms,id'],
        ]);

        $query = Room::withTrashed()
            ->where('dorm_id', $dormId)
            ->whereIn('id', $data['ids'])
            ->with(['floor'])
            ->withCount('activeAssignments');

        $filename = 'rooms_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['room_number', 'floor', 'capacity', 'status', 'occupancy', 'archived']);

            $query->orderBy('room_number')->chunk(200, function ($rooms) use ($handle) {
                foreach ($rooms as $room) {
                    fputcsv($handle, [
                        $room->room_number,
                        $room->floor?->number,
                        $room->capacity,
                        $room->status,
                        $room->active_assignments_count,
                        $room->trashed() ? 'YES' : 'NO',
                    ]);
                }
            });

            fclose($handle);
        }, $filename);
    }
}
