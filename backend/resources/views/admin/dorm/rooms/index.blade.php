@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Rooms</h2>
    <a href="{{ route('admin.dorm.rooms.create') }}" class="btn btn-primary">Add Room</a>
</div>

<form method="GET" action="{{ route('admin.dorm.rooms.index') }}" class="row g-2 mb-3">
    <div class="col-md-4">
        <select name="floor_id" class="form-select" onchange="this.form.submit()">
            <option value="">All floors</option>
            @foreach ($floors as $floor)
                <option value="{{ $floor->id }}" {{ $selectedFloor == $floor->id ? 'selected' : '' }}>
                    Floor {{ $floor->number }}
                </option>
            @endforeach
        </select>
    </div>
</form>

<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Room</th>
        <th>Floor</th>
        <th>Capacity</th>
        <th>Occupancy</th>
        <th>Status</th>
        <th class="text-end">Actions</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($rooms as $room)
        <tr>
            <td>{{ $room->room_number }}</td>
            <td>{{ $room->floor?->number }}</td>
            <td>{{ $room->capacity }}</td>
            <td>{{ $room->active_assignments_count }}</td>
            <td>{{ $room->status }}</td>
            <td class="text-end">
                <a href="{{ route('admin.dorm.rooms.show', $room) }}" class="btn btn-sm btn-outline-primary">Details</a>
                <a href="{{ route('admin.dorm.rooms.edit', $room) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST" action="{{ route('admin.dorm.rooms.destroy', $room) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Delete this room?')">Delete</button>
                </form>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="text-center">No rooms found.</td>
        </tr>
    @endforelse
    </tbody>
</table>
@endsection
