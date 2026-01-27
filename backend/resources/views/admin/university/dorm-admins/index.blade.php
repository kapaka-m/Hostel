@extends('layouts.app')

@section('title', 'Dorm Admins')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h2 class="mb-1">Dorm Admins</h2>
            <div class="text-muted">Manage dorm managers and access levels.</div>
        </div>
        <a href="{{ route('admin.university.dorm-admins.create') }}" class="btn btn-primary">Create Dorm Admin</a>
    </div>

    <form class="row g-2 mb-3" method="GET" action="{{ route('admin.university.dorm-admins.index') }}">
        <div class="col-md-5">
            <input type="search" name="q" class="form-control" placeholder="Search by name, email, dorm"
                value="{{ $filters['q'] }}">
        </div>
        <div class="col-auto d-flex gap-2">
            <button class="btn btn-secondary" type="submit">Search</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.university.dorm-admins.index') }}">Reset</a>
        </div>

    </form>

    <div class="admin-table">
        <table class="table table-striped align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Dorm</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dormAdmins as $dormAdmin)
                    <tr>
                        <td class="fw-semibold">{{ $dormAdmin->user?->name }}</td>
                        <td>{{ $dormAdmin->user?->email }}</td>
                        <td>{{ $dormAdmin->dorm?->name }}</td>
                        <td>
                            <span
                                class="admin-pill bg-{{ $dormAdmin->user?->is_active ? 'success' : 'secondary' }} text-white">
                                {{ $dormAdmin->user?->is_active ? 'ACTIVE' : 'INACTIVE' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary"
                                href="{{ route('admin.university.dorm-admins.edit', $dormAdmin) }}">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">No dorm admins found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $dormAdmins->links('pagination::bootstrap-5') }}
    </div>
@endsection
