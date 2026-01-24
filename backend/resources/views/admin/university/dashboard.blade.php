@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>University Dashboard</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.university.dorms.index') }}" class="btn btn-outline-primary btn-sm">Manage Dorms</a>
            <a href="{{ route('admin.university.dorm-admins.create') }}" class="btn btn-primary btn-sm">Create Dorm Admin</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Dorms</h6>
                    <h3 class="mb-0">{{ $dormCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Rooms</h6>
                    <h3 class="mb-0">{{ $roomCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Students</h6>
                    <h3 class="mb-0">{{ $studentCount }}</h3>
                </div>
            </div>
        </div>
    </div>
@endsection
