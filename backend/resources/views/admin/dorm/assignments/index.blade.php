@extends('layouts.app')

@section('title', 'Assignments')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h2 class="mb-1">Assignments</h2>
            <div class="text-muted">Track room allocation history and moves.</div>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.dorm.assignments.index') }}" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="search" name="q" class="form-control" placeholder="Search student or room"
                value="{{ $filters['q'] }}">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                <option value="active" @selected($filters['status'] === 'active')>Active</option>
                <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="room_id" class="form-select">
                <option value="">All rooms</option>
                @foreach ($rooms as $room)
                    <option value="{{ $room->id }}" @selected((string) $filters['room_id'] === (string) $room->id)>
                        Room {{ $room->room_number }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-secondary" type="submit">Apply</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.dorm.assignments.index') }}">Reset</a>
        </div>
    </form>

    <div class="admin-table">
        <table class="table table-striped align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>Room</th>
                    <th>Assigned</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($assignments as $assignment)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $assignment->student?->full_name }}</div>
                            <div class="text-muted small">{{ $assignment->student?->student_no }}</div>
                        </td>
                        <td>
                            Room {{ $assignment->room?->room_number ?? 'N/A' }}
                            <div class="text-muted small">Floor {{ $assignment->room?->floor?->number ?? 'N/A' }}</div>
                        </td>
                        <td>
                            <div>{{ $assignment->from_date?->format('M d, Y') }}</div>
                            <div class="text-muted small">{{ $assignment->to_date?->format('M d, Y') ?? 'Present' }}</div>
                        </td>
                        <td>
                            @if ($assignment->active)
                                <span class="admin-pill bg-success text-white">Active</span>
                            @else
                                <span class="admin-pill bg-secondary text-white">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if ($assignment->active)
                                <a href="{{ route('admin.dorm.assignments.move', $assignment) }}"
                                    class="btn btn-sm btn-outline-primary">Move</a>
                            @endif
                            <a href="{{ route('admin.dorm.students.show', $assignment->student) }}"
                                class="btn btn-sm btn-outline-secondary">Student</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">No assignments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $assignments->links('pagination::bootstrap-5') }}
    </div>
@endsection
