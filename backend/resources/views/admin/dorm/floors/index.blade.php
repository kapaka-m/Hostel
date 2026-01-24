@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Floors</h2>
    <a href="{{ route('admin.dorm.floors.create') }}" class="btn btn-primary">Add Floor</a>
</div>

<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Number</th>
        <th>Bathrooms</th>
        <th>Kitchens</th>
        <th>Showers</th>
        <th class="text-end">Actions</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($floors as $floor)
        <tr>
            <td>{{ $floor->number }}</td>
            <td>{{ $floor->bathrooms }}</td>
            <td>{{ $floor->kitchens }}</td>
            <td>{{ $floor->showers }}</td>
            <td class="text-end">
                <a href="{{ route('admin.dorm.floors.edit', $floor) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST" action="{{ route('admin.dorm.floors.destroy', $floor) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Delete this floor?')">Delete</button>
                </form>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center">No floors found.</td>
        </tr>
    @endforelse
    </tbody>
</table>
@endsection
