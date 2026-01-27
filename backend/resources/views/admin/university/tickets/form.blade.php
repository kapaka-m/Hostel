@extends('layouts.app')

@section('title', $ticket->exists ? 'Edit Ticket' : 'Create Ticket')

@section('content')
    <div class="mb-3">
        <h2 class="mb-1">{{ $ticket->exists ? 'Edit Ticket' : 'Create Ticket' }}</h2>
        <div class="text-muted">Log and manage maintenance issues across dorms.</div>
    </div>

    <form method="POST"
        action="{{ $ticket->exists ? route('admin.university.tickets.update', $ticket) : route('admin.university.tickets.store') }}"
        class="card shadow-sm">
        @csrf
        @if ($ticket->exists)
            @method('PUT')
        @endif
        <div class="card-body">
            @include('admin.tickets._form', [
                'ticket' => $ticket,
                'dorms' => $dorms ?? collect(),
                'assignees' => $assignees ?? collect(),
            ])
        </div>
        <div class="card-footer border-0 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Save</button>
            <a href="{{ route('admin.university.tickets.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection
