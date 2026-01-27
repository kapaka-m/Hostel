@extends('layouts.app')

@section('title', 'Search')

@section('content')
    @php
        $user = auth()->user();
    @endphp
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1">Search</h2>
            <div class="text-muted">Results for "{{ $query }}"</div>
        </div>
        <form method="GET" action="{{ route('admin.search') }}" class="d-flex gap-2">
            <input type="search" name="q" value="{{ $query }}" class="form-control"
                placeholder="Search dorms, rooms, students" required>
            <button class="btn btn-primary" type="submit">Search</button>
        </form>
    </div>

    @if ($query === '' || strlen($query) < 2)
        <div class="card admin-card">
            <div class="card-body">
                <div class="text-muted">Enter at least 2 characters to search across dorms, rooms, and students.</div>
            </div>
        </div>
    @else
        <div class="row g-4">
            <div class="col-xl-4">
                <div class="card admin-card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Dorms</h5>
                        @if ($dorms->isEmpty())
                            <div class="text-muted">No matching dorms.</div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($dorms as $dorm)
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-semibold">{{ $dorm->name }}</div>
                                                <div class="small text-muted">{{ $dorm->address }}</div>
                                            </div>
                                            @if ($user?->role === \App\Models\User::ROLE_UNIVERSITY_ADMIN && Route::has('admin.university.dorms.edit'))
                                                <a class="btn btn-sm btn-outline-secondary"
                                                    href="{{ route('admin.university.dorms.edit', $dorm) }}">Edit</a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card admin-card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Rooms</h5>
                        @if ($rooms->isEmpty())
                            <div class="text-muted">No matching rooms.</div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($rooms as $room)
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-semibold">Room {{ $room->room_number }}</div>
                                                <div class="small text-muted">Floor {{ $room->floor?->number }} -
                                                    {{ $room->active_assignments_count }}/{{ $room->capacity }}</div>
                                            </div>
                                            @if ($user?->role === \App\Models\User::ROLE_DORM_ADMIN && Route::has('admin.dorm.rooms.show'))
                                                <a class="btn btn-sm btn-outline-secondary"
                                                    href="{{ route('admin.dorm.rooms.show', $room) }}">View</a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card admin-card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Students</h5>
                        @if ($students->isEmpty())
                            <div class="text-muted">No matching students.</div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($students as $student)
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-semibold">{{ $student->full_name }}</div>
                                                <div class="small text-muted">{{ $student->student_no }} -
                                                    {{ $student->user?->email }}</div>
                                            </div>
                                            @if ($user?->role === \App\Models\User::ROLE_DORM_ADMIN && Route::has('admin.dorm.students.edit'))
                                                <a class="btn btn-sm btn-outline-secondary"
                                                    href="{{ route('admin.dorm.students.edit', $student) }}">Edit</a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
