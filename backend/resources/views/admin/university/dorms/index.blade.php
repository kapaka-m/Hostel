@extends('layouts.app')

@section('title', 'Dorms')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h2 class="mb-1">Dorms</h2>
            <div class="text-muted">Manage inventory, capacity, and status.</div>
        </div>
        <a href="{{ route('admin.university.dorms.create') }}" class="btn btn-primary">Add Dorm</a>
    </div>

    <form class="row g-2 mb-3" method="GET" action="{{ route('admin.university.dorms.index') }}">
        <div class="col-md-4">
            <input type="search" name="q" class="form-control" placeholder="Search by name, code, address"
                value="{{ $filters['q'] }}">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                <option value="ACTIVE" @selected($filters['status'] === 'ACTIVE')>Active</option>
                <option value="INACTIVE" @selected($filters['status'] === 'INACTIVE')>Inactive</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="sort" class="form-select">
                <option value="name" @selected($filters['sort'] === 'name')>Sort: Name</option>
                <option value="capacity" @selected($filters['sort'] === 'capacity')>Sort: Capacity</option>
                <option value="status" @selected($filters['sort'] === 'status')>Sort: Status</option>
                <option value="created_at" @selected($filters['sort'] === 'created_at')>Sort: Newest</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="direction" class="form-select">
                <option value="asc" @selected($filters['direction'] === 'asc')>Ascending</option>
                <option value="desc" @selected($filters['direction'] === 'desc')>Descending</option>
            </select>
        </div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-secondary" type="submit">Apply</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.university.dorms.index') }}">Reset</a>
        </div>
    </form>

    <div class="admin-table">
        <table class="table table-striped align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Capacity</th>
                    <th>Rooms</th>
                    <th>Students</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dorms as $dorm)
                    <tr>
                        <td>{{ $dorm->code ?? 'N/A' }}</td>
                        <td class="fw-semibold">{{ $dorm->name }}</td>
                        <td>{{ $dorm->address }}</td>
                        <td>{{ $dorm->capacity }}</td>
                        <td>{{ $dorm->rooms_count }}</td>
                        <td>{{ $dorm->students_count }}</td>
                        <td>
                            <span
                                class="admin-pill bg-{{ $dorm->status === 'ACTIVE' ? 'success' : 'secondary' }} text-white">
                                {{ $dorm->status }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.university.dorms.edit', $dorm) }}"
                                class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="{{ route('admin.university.dorms.destroy', $dorm) }}"
                                class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger js-confirm" type="submit"
                                    data-confirm="Delete this dorm?">Delete</button>

                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">No dorms found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $dorms->links('pagination::bootstrap-5') }}
    </div>
@endsection
