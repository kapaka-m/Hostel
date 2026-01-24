@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Students</h2>
    <a href="{{ route('admin.dorm.students.create') }}" class="btn btn-primary">Add Student</a>
</div>

<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Full Name</th>
        <th>Student No</th>
        <th>Email</th>
        <th>Phone</th>
        <th class="text-end">Actions</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($students as $student)
        <tr>
            <td>{{ $student->full_name }}</td>
            <td>{{ $student->student_no }}</td>
            <td>{{ $student->user?->email }}</td>
            <td>{{ $student->phone }}</td>
            <td class="text-end">
                <a href="{{ route('admin.dorm.students.edit', $student) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST" action="{{ route('admin.dorm.students.destroy', $student) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Delete this student?')">Delete</button>
                </form>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center">No students found.</td>
        </tr>
    @endforelse
    </tbody>
</table>
@endsection
