@extends('layouts.app')

@section('title', $announcement->exists ? 'Edit Announcement' : 'Create Announcement')

@section('content')
    <div class="mb-3">
        <h2 class="mb-1">{{ $announcement->exists ? 'Edit Announcement' : 'Create Announcement' }}</h2>
        <div class="text-muted">Share updates with dorm residents.</div>
    </div>

    <form method="POST"
        action="{{ $announcement->exists ? route('admin.dorm.announcements.update', $announcement) : route('admin.dorm.announcements.store') }}"
        class="card shadow-sm">
        @csrf
        @if ($announcement->exists)
            @method('PUT')
        @endif
        <input type="hidden" name="audience" value="DORM">
        <input type="hidden" name="dorm_id" value="{{ auth()->user()?->dormAdmin?->dorm_id }}">
        <div class="card-body">
            @include('admin.announcements._form', [
                'announcement' => $announcement,
                'dorms' => collect(),
                'showAudience' => false,
            ])
        </div>
        <div class="card-footer border-0 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Save</button>
            <a href="{{ route('admin.dorm.announcements.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection
