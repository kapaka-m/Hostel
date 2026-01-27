@extends('layouts.app')

@section('title', 'Edit Dorm Admin')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="mb-0">Edit Dorm Admin</h2>
        <a href="{{ route('admin.university.dorm-admins.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <form method="POST" action="{{ route('admin.university.dorm-admins.update', $dormAdmin) }}">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Dorm</label>
                <select name="dorm_id" class="form-select @error('dorm_id') is-invalid @enderror" required>
                    <option value="">Select dorm</option>
                    @foreach ($dorms as $dorm)
                        <option value="{{ $dorm->id }}" @selected(old('dorm_id', $dormAdmin->dorm_id) == $dorm->id)>
                            {{ $dorm->name }}
                        </option>
                    @endforeach
                </select>
                @error('dorm_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select @error('is_active') is-invalid @enderror" required>
                    <option value="1" @selected(old('is_active', (int) $dormAdmin->user?->is_active) === 1)>Active</option>
                    <option value="0" @selected(old('is_active', (int) $dormAdmin->user?->is_active) === 0)>Inactive</option>
                </select>
                @error('is_active')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Freeze Reason (optional)</label>
                <input type="text" name="freeze_reason" class="form-control @error('freeze_reason') is-invalid @enderror"
                    value="{{ old('freeze_reason') }}">
                @error('freeze_reason')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $dormAdmin->user?->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $dormAdmin->user?->email) }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Save Changes</button>
            <a href="{{ route('admin.university.dorm-admins.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection
