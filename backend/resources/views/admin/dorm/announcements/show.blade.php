@extends('layouts.app')

@section('title', 'Announcement #' . $announcement->id)

@section('content')
    @php
        $statusColors = [
            'DRAFT' => 'secondary',
            'SCHEDULED' => 'info',
            'PUBLISHED' => 'success',
            'EXPIRED' => 'danger',
        ];
        $currentStatus = $announcement->currentStatus();
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h2 class="mb-1">{{ $announcement->title }}</h2>
            <div class="text-muted">Announcement #{{ $announcement->id }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.dorm.announcements.edit', $announcement) }}"
                class="btn btn-outline-secondary btn-sm">Edit</a>
            <a href="{{ route('admin.dorm.announcements.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Details</h5>
                    <div class="mb-2">
                        <span class="admin-pill bg-{{ $statusColors[$currentStatus] ?? 'secondary' }} text-white">
                            {{ ucfirst(strtolower($currentStatus)) }}
                        </span>
                    </div>
                    <div class="text-muted small mb-1">Publish At</div>
                    <div class="fw-semibold mb-2">{{ $announcement->publish_at?->format('M d, Y H:i') ?? 'TBD' }}</div>
                    <div class="text-muted small mb-1">Expire At</div>
                    <div class="fw-semibold">{{ $announcement->expire_at?->format('M d, Y H:i') ?? 'No expiry' }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Body</h5>
                    <p class="mb-0">{{ $announcement->body }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
