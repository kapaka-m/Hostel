@extends('layouts.app')

@section('title', 'System Dashboard')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h2 class="mb-1">System Dashboard</h2>
            <div class="text-muted">Platform overview for super admins.</div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Universities</div>
                    <div class="fs-4 fw-semibold">{{ $universityCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Dorms</div>
                    <div class="fs-4 fw-semibold">{{ $dormCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Users</div>
                    <div class="fs-4 fw-semibold">{{ $userCount }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
