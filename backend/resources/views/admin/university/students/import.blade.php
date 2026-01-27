@extends('layouts.app')

@section('title', 'Import Students')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="mb-0">Import Students</h2>
        <a href="{{ route('admin.university.students.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card admin-card mb-4">
        <div class="card-body">
            <h5 class="card-title">CSV Format</h5>
            <p class="text-muted mb-2">Required headers: <strong>full_name, student_no, email</strong></p>
            <p class="text-muted mb-0">Optional headers: <strong>phone, dorm_code</strong>. If no dorm_code, select a default
                dorm below.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.university.students.import.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Default Dorm (optional)</label>
                <select name="default_dorm_id" class="form-select @error('default_dorm_id') is-invalid @enderror">
                    <option value="">No default dorm</option>
                    @foreach ($dorms as $dorm)
                        <option value="{{ $dorm->id }}" @selected(old('default_dorm_id') == $dorm->id)>
                            {{ $dorm->name }}
                        </option>
                    @endforeach
                </select>
                @error('default_dorm_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">CSV File</label>
                <input type="file" name="file" class="form-control @error('file') is-invalid @enderror"
                    accept=".csv,.txt" required>
                @error('file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Import Students</button>
            <a href="{{ route('admin.university.students.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection
