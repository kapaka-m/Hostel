@extends('layouts.app')

@section('title', $student->exists ? 'Edit Student' : 'Create Student')

@section('content')
    <div class="mb-3">
        <h2 class="mb-1">{{ $student->exists ? 'Edit Student' : 'Create Student' }}</h2>
        <div class="text-muted">Capture contact details and student identifiers.</div>
    </div>

    <form method="POST"
        action="{{ $student->exists ? route('admin.dorm.students.update', $student) : route('admin.dorm.students.store') }}"
        class="card shadow-sm">
        @csrf
        @if ($student->exists)
            @method('PUT')
        @endif
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control"
                        value="{{ old('full_name', $student->full_name) }}" required>
                    @error('full_name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Student Number</label>
                    <input type="text" name="student_no" class="form-control"
                        value="{{ old('student_no', $student->student_no) }}" required>
                    @error('student_no')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                        value="{{ old('email', $student->user?->email) }}" required>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $student->phone) }}">
                    @error('phone')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        <div class="card-footer border-0 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Save</button>
            <a href="{{ route('admin.dorm.students.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection
