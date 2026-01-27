@extends('layouts.app')

@section('title', 'Students')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h2 class="mb-1">Students</h2>
            <div class="text-muted">University-wide overview and exports.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.university.students.import') }}" class="btn btn-outline-secondary">Import CSV</a>
            <a href="{{ route('admin.university.students.export', request()->query()) }}" class="btn btn-primary">Export
                CSV</a>
        </div>
    </div>

    <form class="row g-2 mb-3" method="GET" action="{{ route('admin.university.students.index') }}">
        <div class="col-md-4">
            <input type="search" name="q" class="form-control" placeholder="Search name, student no, email"
                value="{{ $filters['q'] }}">
        </div>
        <div class="col-md-3">
            <select name="dorm_id" class="form-select">
                <option value="">All dorms</option>
                @foreach ($dorms as $dorm)
                    <option value="{{ $dorm->id }}" @selected((string) $filters['dorm_id'] === (string) $dorm->id)>
                        {{ $dorm->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                <option value="ACTIVE" @selected($filters['status'] === 'ACTIVE')>Active</option>
                <option value="INACTIVE" @selected($filters['status'] === 'INACTIVE')>Inactive</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-secondary" type="submit">Apply</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.university.students.index') }}">Reset</a>
        </div>
    </form>

    <div class="admin-table">
        <table class="table table-striped align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Student No</th>
                    <th>Email</th>
                    <th>Dorm</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                    <tr>
                        <td class="fw-semibold">{{ $student->full_name }}</td>
                        <td>{{ $student->student_no }}</td>
                        <td>{{ $student->user?->email }}</td>
                        <td>{{ $student->dorm?->name }}</td>
                        <td>
                            <span
                                class="admin-pill bg-{{ $student->user?->is_active ? 'success' : 'secondary' }} text-white">
                                {{ $student->user?->is_active ? 'ACTIVE' : 'INACTIVE' }}
                            </span>
                        </td>
                        <td class="text-end">
                            @if (Route::has('admin.university.students.show'))
                                <a href="{{ route('admin.university.students.show', $student) }}"
                                    class="btn btn-sm btn-outline-secondary">View</a>
                            @else
                                <span class="text-muted small">No actions</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">No students found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $students->links('pagination::bootstrap-5') }}
    </div>
@endsection
