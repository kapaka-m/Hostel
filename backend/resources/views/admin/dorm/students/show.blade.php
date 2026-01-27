@extends('layouts.app')

@section('title', 'Student ' . $student->full_name)

@section('content')
    @php
        $currentAssignment = $student->activeAssignment;
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h2 class="mb-1">{{ $student->full_name }}</h2>
            <div class="text-muted">Student No: {{ $student->student_no }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.dorm.students.edit', $student) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
            <a href="{{ route('admin.dorm.students.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Profile</h5>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-muted small">Email</div>
                            <div class="fw-semibold">{{ $student->user?->email ?? 'N/A' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Phone</div>
                            <div class="fw-semibold">{{ $student->phone ?? 'N/A' }}</div>
                        </div>
                    </div>
                    @if ($student->user)
                        <div class="mt-3">
                            <div class="text-muted small">Account Status</div>
                            @if ($student->user->isFrozen())
                                <span class="admin-pill bg-danger text-white">Frozen</span>
                                <div class="text-muted small mt-1">Frozen at
                                    {{ $student->user->frozen_at?->format('M d, Y H:i') ?? 'N/A' }}</div>
                                <form method="POST" action="{{ route('admin.dorm.students.unfreeze', $student) }}"
                                    class="mt-2">
                                    @csrf
                                    <div class="mb-2">
                                        <input type="text" name="reason" class="form-control"
                                            placeholder="Reason for reactivation (optional)">
                                    </div>
                                    <button class="btn btn-sm btn-outline-success" type="submit">Unfreeze Account</button>
                                </form>
                            @else
                                <span class="admin-pill bg-success text-white">Active</span>
                                <form method="POST" action="{{ route('admin.dorm.students.freeze', $student) }}"
                                    class="mt-2">
                                    @csrf
                                    <div class="mb-2">
                                        <input type="text" name="reason" class="form-control"
                                            placeholder="Reason for freezing (optional)">
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Freeze Account</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Current assignment</h5>
                    @if ($currentAssignment && $currentAssignment->room)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <div class="fw-semibold">Room {{ $currentAssignment->room->room_number }}</div>
                                <div class="text-muted small">Floor {{ $currentAssignment->room->floor?->number ?? 'N/A' }}
                                </div>
                            </div>
                            <span class="admin-pill bg-success text-white">Active</span>
                        </div>
                        <div class="text-muted small mb-3">
                            Assigned since {{ $currentAssignment->from_date?->format('M d, Y') ?? 'N/A' }}
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('admin.dorm.rooms.show', $currentAssignment->room) }}"
                                class="btn btn-sm btn-outline-primary">View room</a>
                            <form method="POST" action="{{ route('admin.dorm.students.unassign', $student) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger" type="submit"
                                    onclick="return confirm('Unassign this student?')">Unassign</button>
                            </form>
                        </div>
                    @else
                        <div class="text-muted">No active assignment yet.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5 class="card-title">Assignment history</h5>
            <div class="timeline">
                @forelse ($assignments as $assignment)
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <div class="fw-semibold">
                                        Room {{ $assignment->room?->room_number ?? 'N/A' }}
                                    </div>
                                    <div class="text-muted small">Floor {{ $assignment->room?->floor?->number ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="text-muted small">
                                    {{ $assignment->from_date?->format('M d, Y') ?? 'N/A' }}
                                    -
                                    {{ $assignment->to_date?->format('M d, Y') ?? 'Present' }}
                                </div>
                            </div>
                            @if ($assignment->active)
                                <span class="admin-pill bg-success text-white mt-2">Active</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted">No assignments yet.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
