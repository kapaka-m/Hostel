<?php

namespace App\Services;

use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\Student;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoomAssignmentService
{
    public function assignStudentToRoom(Student $student, Room $room): RoomAssignment
    {
        if ($student->dorm_id !== $room->dorm_id) {
            throw ValidationException::withMessages([
                'student_id' => ['Student is not in this dorm.'],
            ]);
        }

        return DB::transaction(function () use ($student, $room) {
            $existing = RoomAssignment::where('student_id', $student->id)
                ->where('active', true)
                ->first();

            $currentCount = RoomAssignment::where('room_id', $room->id)
                ->where('active', true)
                ->count();

            if ($existing && $existing->room_id === $room->id) {
                return $existing;
            }

            if ($currentCount >= $room->capacity) {
                throw ValidationException::withMessages([
                    'room_id' => ['Room is full.'],
                ]);
            }

            if ($existing) {
                $existing->active = false;
                $existing->to_date = now();
                $existing->save();
                $this->updateRoomStatus($existing->room_id);
            }

            $assignment = RoomAssignment::create([
                'student_id' => $student->id,
                'room_id' => $room->id,
                'active' => true,
                'from_date' => now(),
            ]);

            $this->updateRoomStatus($room->id);
            app(AuditLogger::class)->log('assign_student', $assignment, null, [
                'student_id' => $student->id,
                'room_id' => $room->id,
            ]);

            return $assignment;
        });
    }

    public function deactivateStudentAssignment(Student $student): void
    {
        $assignment = RoomAssignment::where('student_id', $student->id)
            ->where('active', true)
            ->first();

        if (!$assignment) {
            return;
        }

        $assignment->active = false;
        $assignment->to_date = now();
        $assignment->save();

        app(AuditLogger::class)->log('unassign_student', $assignment, null, [
            'student_id' => $student->id,
            'room_id' => $assignment->room_id,
        ]);

        $this->updateRoomStatus($assignment->room_id);
    }

    public function updateRoomStatus(int $roomId): void
    {
        $room = Room::find($roomId);

        if (!$room) {
            return;
        }

        $count = RoomAssignment::where('room_id', $roomId)
            ->where('active', true)
            ->count();

        if ($count <= 0) {
            $status = 'AVAILABLE';
        } elseif ($count < $room->capacity) {
            $status = 'PARTIAL';
        } else {
            $status = 'FULL';
        }

        $room->status = $status;
        $room->save();
    }
}
