@extends('layouts.app')

@section('content')
<h2 class="mb-3">{{ $room->exists ? 'Edit Room' : 'Create Room' }}</h2>

<form method="POST" action="{{ $room->exists ? route('admin.dorm.rooms.update', $room) : route('admin.dorm.rooms.store') }}">
    @csrf
    @if ($room->exists)
        @method('PUT')
    @endif

    <div class="mb-3">
        <label class="form-label">Floor</label>
        <select name="floor_id" class="form-select" required>
            <option value="">Select floor</option>
            @foreach ($floors as $floor)
                <option value="{{ $floor->id }}" {{ old('floor_id', $room->floor_id) == $floor->id ? 'selected' : '' }}>
                    Floor {{ $floor->number }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Room Number</label>
        <input type="text" name="room_number" class="form-control" value="{{ old('room_number', $room->room_number) }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Capacity</label>
        <input type="number" name="capacity" class="form-control" value="{{ old('capacity', $room->capacity ?? 4) }}">
    </div>

    <button class="btn btn-primary" type="submit">Save</button>
    <a href="{{ route('admin.dorm.rooms.index') }}" class="btn btn-outline-secondary">Cancel</a>
</form>
@endsection
