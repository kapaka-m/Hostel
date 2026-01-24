@extends('layouts.app')

@section('content')
    <h2 class="mb-3">Create Dorm Admin</h2>

    <form method="POST" action="{{ route('admin.university.dorm-admins.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Dorm</label>
            <select name="dorm_id" class="form-select" required>
                <option value="">Select dorm</option>
                @foreach ($dorms as $dorm)
                    <option value="{{ $dorm->id }}" {{ old('dorm_id') == $dorm->id ? 'selected' : '' }}>
                        {{ $dorm->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button class="btn btn-primary" type="submit">Create Admin</button>
        <a href="{{ route('admin.university.dorms.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
