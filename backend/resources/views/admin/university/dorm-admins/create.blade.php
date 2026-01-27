@extends('layouts.app')

@section('title', 'Create Dorm Admin')

@section('content')
    <h2 class="mb-3">Create Dorm Admin</h2>

    <form method="POST" action="{{ route('admin.university.dorm-admins.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Dorm</label>
            <select name="dorm_id" class="form-select @error('dorm_id') is-invalid @enderror" required>
                <option value="">Select dorm</option>
                @foreach ($dorms as $dorm)
                    <option value="{{ $dorm->id }}" {{ old('dorm_id') == $dorm->id ? 'selected' : '' }}>
                        {{ $dorm->name }}</option>
                @endforeach
            </select>
            @error('dorm_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email') }}" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button class="btn btn-primary" type="submit">Create Admin</button>
        <a href="{{ route('admin.university.dorm-admins.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
