@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Room {{ $room->room_number }}</h2>
    <a href="{{ route('admin.dorm.rooms.index') }}" class="btn btn-outline-secondary btn-sm">Back to Rooms</a>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Details</h5>
                <p class="mb-1"><strong>Floor:</strong> {{ $room->floor?->number }}</p>
                <p class="mb-1"><strong>Capacity:</strong> {{ $room->capacity }}</p>
                <p class="mb-1"><strong>Occupancy:</strong> {{ $room->active_assignments_count }}</p>
                <p class="mb-0"><strong>Status:</strong> {{ $room->status }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Assign Student</h5>
                <form method="POST" action="{{ route('admin.dorm.rooms.assign', $room) }}">
                    @csrf
                    <div class="mb-3">
                        <select name="student_id" class="form-select" required>
                            <option value="">Select student</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->student_no }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary" type="submit">Assign</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-body">
        <h5 class="card-title">Occupants</h5>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Full Name</th>
                <th>Student No</th>
                <th>Email</th>
                <th>Phone</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($occupants as $occupant)
                <tr>
                    <td>{{ $occupant->full_name }}</td>
                    <td>{{ $occupant->student_no }}</td>
                    <td>{{ $occupant->user?->email }}</td>
                    <td>{{ $occupant->phone }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No occupants yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
