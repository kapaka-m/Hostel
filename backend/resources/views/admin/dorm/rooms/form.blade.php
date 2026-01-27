@extends('layouts.app')

@section('title', $room->exists ? 'Edit Room' : 'Create Room')

@section('content')
    <div class="mb-3">
        <h2 class="mb-1">{{ $room->exists ? 'Edit Room' : 'Create Room' }}</h2>
        <div class="text-muted">Define room capacity and assign it to a floor.</div>
    </div>

    <form method="POST"
        action="{{ $room->exists ? route('admin.dorm.rooms.update', $room) : route('admin.dorm.rooms.store') }}"
        class="card shadow-sm">
        @csrf
        @if ($room->exists)
            @method('PUT')
        @endif
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Floor</label>
                    <select name="floor_id" class="form-select" required>
                        <option value="">Select floor</option>
                        @foreach ($floors as $floor)
                            <option value="{{ $floor->id }}"
                                {{ old('floor_id', $room->floor_id) == $floor->id ? 'selected' : '' }}>
                                Floor {{ $floor->number }}
                            </option>
                        @endforeach
                    </select>
                    @error('floor_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Room Number</label>
                    <input type="text" name="room_number" class="form-control"
                        value="{{ old('room_number', $room->room_number) }}" required>
                    @error('room_number')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Capacity</label>
                    <input type="number" name="capacity" class="form-control"
                        value="{{ old('capacity', $room->capacity ?? 4) }}">
                    @error('capacity')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        <div class="card-footer border-0 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Save</button>
            <a href="{{ route('admin.dorm.rooms.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection
