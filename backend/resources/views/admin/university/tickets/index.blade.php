@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
    @php
        $statusColors = [
            'OPEN' => 'primary',
            'IN_PROGRESS' => 'warning',
            'RESOLVED' => 'success',
            'CLOSED' => 'secondary',
        ];
        $priorityColors = [
            'LOW' => 'secondary',
            'MEDIUM' => 'info',
            'HIGH' => 'warning',
            'URGENT' => 'danger',
        ];
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h2 class="mb-1">Tickets</h2>
            <div class="text-muted">Track maintenance issues across dorms.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.university.tickets.export', request()->query()) }}"
                class="btn btn-outline-secondary">Export CSV</a>
            <a href="{{ route('admin.university.tickets.create') }}" class="btn btn-primary">New Ticket</a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.university.tickets.index') }}" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="search" name="q" class="form-control" placeholder="Search subject, category, requester"
                value="{{ $filters['q'] }}">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach (\App\Models\Ticket::STATUSES as $status)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>
                        {{ ucfirst(strtolower(str_replace('_', ' ', $status))) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="priority" class="form-select">
                <option value="">All priorities</option>
                @foreach (\App\Models\Ticket::PRIORITIES as $priority)
                    <option value="{{ $priority }}" @selected($filters['priority'] === $priority)>
                        {{ ucfirst(strtolower($priority)) }}
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
            <a class="btn btn-outline-secondary" href="{{ route('admin.university.tickets.index') }}">Reset</a>
        </div>
    </form>

    <div class="admin-table">
        <table class="table table-striped align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Ticket</th>
                    <th>Dorm</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Assignee</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets as $ticket)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $ticket->subject }}</div>
                            <div class="text-muted small">{{ $ticket->category ?? 'General' }}</div>
                        </td>
                        <td>{{ $ticket->dorm?->name ?? 'University-wide' }}</td>
                        <td>
                            <span class="admin-pill bg-{{ $priorityColors[$ticket->priority] ?? 'secondary' }} text-white">
                                {{ ucfirst(strtolower($ticket->priority)) }}
                            </span>
                        </td>
                        <td>
                            <span class="admin-pill bg-{{ $statusColors[$ticket->status] ?? 'secondary' }} text-white">
                                {{ ucfirst(strtolower(str_replace('_', ' ', $ticket->status))) }}
                            </span>
                        </td>
                        <td>{{ $ticket->assignee?->name ?? 'Unassigned' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.university.tickets.show', $ticket) }}"
                                class="btn btn-sm btn-outline-primary">View</a>
                            <a href="{{ route('admin.university.tickets.edit', $ticket) }}"
                                class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">No tickets found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $tickets->links('pagination::bootstrap-5') }}
    </div>
@endsection
