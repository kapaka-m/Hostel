@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Housing Dashboard</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.dorm.floors.index') }}" class="btn btn-outline-primary btn-sm">Floors</a>
            <a href="{{ route('admin.dorm.rooms.index') }}" class="btn btn-outline-primary btn-sm">Rooms</a>
            <a href="{{ route('admin.dorm.students.index') }}" class="btn btn-outline-primary btn-sm">Students</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Floors</h6>
                    <h3 class="mb-0">{{ $floorCount }}</h3>
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
