@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->id)

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
            <h2 class="mb-1">{{ $ticket->subject }}</h2>
            <div class="text-muted">Ticket #{{ $ticket->id }} · {{ $ticket->category ?? 'General' }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.university.tickets.edit', $ticket) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
            <a href="{{ route('admin.university.tickets.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Details</h5>
                    <div class="mb-2">
                        <span class="admin-pill bg-{{ $statusColors[$ticket->status] ?? 'secondary' }} text-white">
                            {{ ucfirst(strtolower(str_replace('_', ' ', $ticket->status))) }}
                        </span>
                        <span class="admin-pill bg-{{ $priorityColors[$ticket->priority] ?? 'secondary' }} text-white ms-1">
                            {{ ucfirst(strtolower($ticket->priority)) }}
                        </span>
                    </div>
                    <div class="text-muted small mb-1">Dorm</div>
                    <div class="fw-semibold mb-2">{{ $ticket->dorm?->name ?? 'University-wide' }}</div>
                    <div class="text-muted small mb-1">Assigned To</div>
                    <div class="fw-semibold mb-2">{{ $ticket->assignee?->name ?? 'Unassigned' }}</div>
                    <div class="text-muted small mb-1">Created By</div>
                    <div class="fw-semibold mb-2">{{ $ticket->creator?->name ?? 'N/A' }}</div>
                    <div class="text-muted small mb-1">Created</div>
                    <div class="fw-semibold mb-2">{{ $ticket->created_at?->format('M d, Y H:i') }}</div>
                    <div class="text-muted small mb-1">Resolved</div>
                    <div class="fw-semibold">{{ $ticket->resolved_at?->format('M d, Y H:i') ?? 'Not resolved' }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Description</h5>
                    <p class="mb-0">{{ $ticket->description }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5 class="card-title">Comments</h5>
            <div class="mb-3">
                @forelse ($ticket->comments as $comment)
                    <div class="border rounded-3 p-3 mb-2">
                        <div class="d-flex justify-content-between">
                            <div class="fw-semibold">{{ $comment->user?->name ?? 'Unknown' }}</div>
                            <div class="text-muted small">{{ $comment->created_at?->format('M d, Y H:i') }}</div>
                        </div>
                        <div class="mt-2">{{ $comment->body }}</div>
                    </div>
                @empty
                    <div class="text-muted">No comments yet.</div>
                @endforelse
            </div>
            <form method="POST" action="{{ route('admin.university.tickets.comment', $ticket) }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label">Add comment</label>
                    <textarea name="body" class="form-control" rows="3" required>{{ old('body') }}</textarea>
                    @error('body')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <button class="btn btn-primary btn-sm" type="submit">Post Comment</button>
            </form>
        </div>
    </div>
@endsection
