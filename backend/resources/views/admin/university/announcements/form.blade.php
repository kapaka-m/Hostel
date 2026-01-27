@extends('layouts.app')

@section('title', $announcement->exists ? 'Edit Announcement' : 'Create Announcement')

@section('content')
    <div class="mb-3">
        <h2 class="mb-1">{{ $announcement->exists ? 'Edit Announcement' : 'Create Announcement' }}</h2>
        <div class="text-muted">Schedule announcements and target dorm audiences.</div>
    </div>

    <form method="POST"
        action="{{ $announcement->exists ? route('admin.university.announcements.update', $announcement) : route('admin.university.announcements.store') }}"
        class="card shadow-sm">
        @csrf
        @if ($announcement->exists)
            @method('PUT')
        @endif
        <div class="card-body">
            @include('admin.announcements._form', [
                'announcement' => $announcement,
                'dorms' => $dorms ?? collect(),
                'showAudience' => true,
            ])
        </div>
        <div class="card-footer border-0 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Save</button>
            <a href="{{ route('admin.university.announcements.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection
