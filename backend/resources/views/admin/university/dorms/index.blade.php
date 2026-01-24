@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Dorms</h2>
    <a href="{{ route('admin.university.dorms.create') }}" class="btn btn-primary">Add Dorm</a>
</div>

<table class="table table-striped table-bordered">
    <thead>
    <tr>
        <th>Name</th>
        <th>Address</th>
        <th class="text-end">Actions</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($dorms as $dorm)
        <tr>
            <td>{{ $dorm->name }}</td>
            <td>{{ $dorm->address }}</td>
            <td class="text-end">
                <a href="{{ route('admin.university.dorms.edit', $dorm) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST" action="{{ route('admin.university.dorms.destroy', $dorm) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Delete this dorm?')">Delete</button>
                </form>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="3" class="text-center">No dorms found.</td>
        </tr>
    @endforelse
    </tbody>
</table>
@endsection
