@extends('layouts.app')

@section('title', 'Move Assignment')

@section('content')
    <div class="mb-3">
        <h2 class="mb-1">Move Assignment</h2>
        <div class="text-muted">Reassign a student to a different room.</div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-6">
                    <div class="text-muted small">Student</div>
                    <div class="fw-semibold">{{ $assignment->student?->full_name }}</div>
                    <div class="text-muted small">{{ $assignment->student?->student_no }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Current Room</div>
                    <div class="fw-semibold">Room {{ $assignment->room?->room_number ?? 'N/A' }}</div>
                    <div class="text-muted small">Floor {{ $assignment->room?->floor?->number ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.dorm.assignments.update', $assignment) }}" class="card shadow-sm">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">New Room</label>
                <select name="room_id" class="form-select" required>
                    <option value="">Select room</option>
                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}">
                            Room {{ $room->room_number }} (Floor {{ $room->floor?->number ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
                @error('room_id')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="card-footer border-0 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Move Student</button>
            <a href="{{ route('admin.dorm.assignments.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection
