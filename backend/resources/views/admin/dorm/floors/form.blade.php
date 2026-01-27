@extends('layouts.app')

@section('content')
    <h2 class="mb-3">{{ $floor->exists ? 'Edit Floor' : 'Create Floor' }}</h2>

    <form method="POST"
        action="{{ $floor->exists ? route('admin.dorm.floors.update', $floor) : route('admin.dorm.floors.store') }}">
        @csrf
        @if ($floor->exists)
            @method('PUT')
        @endif

        <div class="mb-3">
            <label class="form-label">Number</label>
            <input type="number" name="number" class="form-control" value="{{ old('number', $floor->number) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Bathrooms</label>
            <input type="number" name="bathrooms" class="form-control"
                value="{{ old('bathrooms', $floor->bathrooms ?? 4) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Kitchens</label>
            <input type="number" name="kitchens" class="form-control" value="{{ old('kitchens', $floor->kitchens ?? 2) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Showers</label>
            <input type="number" name="showers" class="form-control" value="{{ old('showers', $floor->showers ?? 2) }}">
        </div>

        <button class="btn btn-primary" type="submit">Save</button>
        <a href="{{ route('admin.dorm.floors.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
