@extends('layouts.app')

@section('title', $dorm->exists ? 'Edit Dorm' : 'Create Dorm')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="mb-0">{{ $dorm->exists ? 'Edit Dorm' : 'Create Dorm' }}</h2>
        <a href="{{ route('admin.university.dorms.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <form method="POST"
        action="{{ $dorm->exists ? route('admin.university.dorms.update', $dorm) : route('admin.university.dorms.store') }}">
        @csrf
        @if ($dorm->exists)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $dorm->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Code</label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                    value="{{ old('code', $dorm->code) }}" placeholder="D-101">
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Capacity</label>
                <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror"
                    value="{{ old('capacity', $dorm->capacity ?? 0) }}" min="0">
                @error('capacity')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                    value="{{ old('address', $dorm->address) }}">
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="ACTIVE" @selected(old('status', $dorm->status ?? 'ACTIVE') === 'ACTIVE')>Active</option>
                    <option value="INACTIVE" @selected(old('status', $dorm->status ?? 'ACTIVE') === 'INACTIVE')>Inactive</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Contact Name</label>
                <input type="text" name="contact_name" class="form-control @error('contact_name') is-invalid @enderror"
                    value="{{ old('contact_name', $dorm->contact_name) }}">
                @error('contact_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Contact Email</label>
                <input type="email" name="contact_email" class="form-control @error('contact_email') is-invalid @enderror"
                    value="{{ old('contact_email', $dorm->contact_email) }}">
                @error('contact_email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Contact Phone</label>
                <input type="text" name="contact_phone" class="form-control @error('contact_phone') is-invalid @enderror"
                    value="{{ old('contact_phone', $dorm->contact_phone) }}">
                @error('contact_phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $dorm->notes) }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Save</button>
            <a href="{{ route('admin.university.dorms.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection
