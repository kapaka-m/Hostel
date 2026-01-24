@extends('layouts.app')

@section('content')
<h2 class="mb-3">{{ $dorm->exists ? 'Edit Dorm' : 'Create Dorm' }}</h2>

<form method="POST" action="{{ $dorm->exists ? route('admin.university.dorms.update', $dorm) : route('admin.university.dorms.store') }}">
    @csrf
    @if ($dorm->exists)
        @method('PUT')
    @endif

    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $dorm->name) }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Address</label>
        <input type="text" name="address" class="form-control" value="{{ old('address', $dorm->address) }}">
    </div>

    <button class="btn btn-primary" type="submit">Save</button>
    <a href="{{ route('admin.university.dorms.index') }}" class="btn btn-outline-secondary">Cancel</a>
</form>
@endsection
