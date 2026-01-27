@extends('layouts.app')

@section('title', 'Room ' . $room->room_number)

@section('content')
    @php
        $assignedCount = $room->active_assignments_count;
        $capacity = max((int) $room->capacity, 1);
        $occupancy = min(100, (int) round(($assignedCount / $capacity) * 100));
        $available = max($room->capacity - $assignedCount, 0);
        $statusColor = match ($room->status) {
            'FULL' => 'danger',
            'PARTIAL' => 'warning',
            default => 'success',
        };
        $unassignedStudents = $students->filter(fn($student) => !$student->activeAssignment);
        $room = $room ?? null;
        $roomId = $room?->id;
        $assignedStudents = $students->filter(
            fn($student) => $student->activeAssignment && $student->activeAssignment->room_id !== $roomId,
        );
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h2 class="mb-1">Room {{ $room->room_number }}</h2>
            <div class="text-muted">Floor {{ $room->floor?->number ?? 'N/A' }} · {{ $available }} beds available</div>
        </div>
        <a href="{{ route('admin.dorm.rooms.index') }}" class="btn btn-outline-secondary btn-sm">Back to Rooms</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Room details</h5>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Status</span>
                        <span class="admin-pill bg-{{ $statusColor }} text-white">{{ $room->status }}</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-muted small">Floor</div>
                            <div class="fw-semibold">Floor {{ $room->floor?->number ?? 'N/A' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Capacity</div>
                            <div class="fw-semibold">{{ $room->capacity }} beds</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">Occupancy</div>
                            <div class="fw-semibold">{{ $assignedCount }}/{{ $room->capacity }}</div>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-{{ $statusColor }}" style="width: {{ $occupancy }}%"></div>
                        </div>
                        <div class="text-muted small mt-2">{{ $available }} available beds</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Assign student</h5>
                    <form method="POST" action="{{ route('admin.dorm.rooms.assign', $room) }}">
                        @csrf
                        <div class="mb-3">
                            <select name="student_id" class="form-select" required>
                                <option value="">Select student</option>
                                @if ($unassignedStudents->isNotEmpty())
                                    <optgroup label="Unassigned">
                                        @foreach ($unassignedStudents as $student)
                                            <option value="{{ $student->id }}">{{ $student->full_name }}
                                                ({{ $student->student_no }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                @if ($assignedStudents->isNotEmpty())
                                    <optgroup label="Move from another room">
                                        @foreach ($assignedStudents as $student)
                                            <option value="{{ $student->id }}">
                                                {{ $student->full_name }} ({{ $student->student_no }}) - Room
                                                {{ $student->activeAssignment?->room?->room_number }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <button class="btn btn-primary" type="submit" @disabled($available <= 0 || $students->isEmpty())>Assign</button>
                            @if ($available <= 0)
                                <div class="text-muted small">Unassign someone to free a bed.</div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5 class="card-title">Current occupants</h5>
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Student No</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Assigned</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $assignment)
                        <tr>
                            <td>
                                @if ($assignment->student)
                                    <a href="{{ route('admin.dorm.students.show', $assignment->student) }}"
                                        class="text-decoration-none">
                                        {{ $assignment->student->full_name }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ $assignment->student?->student_no }}</td>
                            <td>{{ $assignment->student?->user?->email }}</td>
                            <td>{{ $assignment->student?->phone }}</td>
                            <td>{{ $assignment->from_date?->format('M d, Y') }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.dorm.rooms.unassign', $room) }}"
                                    class="d-inline">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $assignment->student_id }}">
                                    <button class="btn btn-sm btn-outline-danger" type="submit"
                                        onclick="return confirm('Unassign this student?')">Unassign</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No occupants yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
