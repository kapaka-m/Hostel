<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DormResource;
use App\Http\Resources\FloorResource;
use App\Http\Resources\RoomResource;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentRoomController extends Controller
{
    public function myRoom(Request $request)
    {
        $student = $request->user()?->student;

        if (!$student) {
            return response()->json([
                'message' => 'Student profile not found.',
                'errors' => [],
            ], 404);
        }

        $assignment = $student->activeAssignment()->with('room.floor.dorm')->first();

        if (!$assignment) {
            return response()->json([
                'message' => 'No active room assignment found.',
                'errors' => [],
                'dorm' => null,
                'floor' => null,
                'room' => null,
                'occupants' => [],
            ]);
        }

        $room = $assignment->room;
        $floor = $room->floor;
        $dorm = $room->dorm;

        $occupants = Student::whereHas('activeAssignment', function ($query) use ($room) {
            $query->where('room_id', $room->id)->where('active', true);
        })
            ->with('user')
            ->orderBy('full_name')
            ->get();

        return response()->json([
            'dorm' => new DormResource($dorm),
            'floor' => new FloorResource($floor),
            'room' => new RoomResource($room->loadCount('activeAssignments')),
            'occupants' => StudentResource::collection($occupants),
        ]);
    }
}
