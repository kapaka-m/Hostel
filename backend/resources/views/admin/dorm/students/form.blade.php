@extends('layouts.app')

@section('content')
<h2 class="mb-3">{{ $student->exists ? 'Edit Student' : 'Create Student' }}</h2>

<form method="POST" action="{{ $student->exists ? route('admin.dorm.students.update', $student) : route('admin.dorm.students.store') }}">
    @csrf
    @if ($student->exists)
        @method('PUT')
    @endif

    <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $student->full_name) }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Student Number</label>
        <input type="text" name="student_no" class="form-control" value="{{ old('student_no', $student->student_no) }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $student->user?->email) }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $student->phone) }}">
    </div>

    <button class="btn btn-primary" type="submit">Save</button>
    <a href="{{ route('admin.dorm.students.index') }}" class="btn btn-outline-secondary">Cancel</a>
</form>
@endsection
