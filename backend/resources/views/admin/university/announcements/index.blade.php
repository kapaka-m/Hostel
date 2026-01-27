@extends('layouts.app')

@section('title', 'Announcements')

@section('content')
    @php
        $statusColors = [
            'DRAFT' => 'secondary',
            'SCHEDULED' => 'info',
            'PUBLISHED' => 'success',
            'EXPIRED' => 'danger',
        ];
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h2 class="mb-1">Announcements</h2>
            <div class="text-muted">Schedule and target dorm communications.</div>
        </div>
        <a href="{{ route('admin.university.announcements.create') }}" class="btn btn-primary">New Announcement</a>
    </div>

    <form method="GET" action="{{ route('admin.university.announcements.index') }}" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="search" name="q" class="form-control" placeholder="Search title or body"
                value="{{ $filters['q'] }}">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach (\App\Models\Announcement::STATUSES as $status)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>
                        {{ ucfirst(strtolower($status)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="audience" class="form-select">
                <option value="">All audiences</option>
                @foreach (\App\Models\Announcement::AUDIENCES as $audience)
                    <option value="{{ $audience }}" @selected($filters['audience'] === $audience)>
                        {{ ucfirst(strtolower($audience)) }}
                    </option>
                @endforeach
            </select>
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
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-secondary" type="submit">Apply</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.university.announcements.index') }}">Reset</a>
        </div>
    </form>

    <div class="admin-table">
        <table class="table table-striped align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Title</th>
                    <th>Audience</th>
                    <th>Status</th>
                    <th>Publish</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($announcements as $announcement)
                    @php $currentStatus = $announcement->currentStatus(); @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $announcement->title }}</div>
                            <div class="text-muted small">{{ $announcement->dorm?->name ?? 'University' }}</div>
                        </td>
                        <td>{{ ucfirst(strtolower($announcement->audience)) }}</td>
                        <td>
                            <span class="admin-pill bg-{{ $statusColors[$currentStatus] ?? 'secondary' }} text-white">
                                {{ ucfirst(strtolower($currentStatus)) }}
                            </span>
                        </td>
                        <td>{{ $announcement->publish_at?->format('M d, Y H:i') ?? 'TBD' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.university.announcements.show', $announcement) }}"
                                class="btn btn-sm btn-outline-primary">View</a>
                            <a href="{{ route('admin.university.announcements.edit', $announcement) }}"
                                class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">No announcements found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $announcements->links('pagination::bootstrap-5') }}
    </div>
@endsection
