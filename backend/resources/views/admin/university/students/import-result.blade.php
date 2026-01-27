@extends('layouts.app')

@section('title', 'Import Results')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="mb-0">Import Results</h2>
        <a href="{{ route('admin.university.students.index') }}" class="btn btn-outline-secondary">Back to Students</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card admin-card h-100">
                <div class="card-body">
                    <h5 class="card-title">Created Accounts</h5>
                    @if (empty($created))
                        <div class="text-muted">No students created.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Row</th>
                                        <th>Student No</th>
                                        <th>Email</th>
                                        <th>Password</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($created as $row)
                                        <tr>
                                            <td>{{ $row['row'] }}</td>
                                            <td>{{ $row['student_no'] }}</td>
                                            <td>{{ $row['email'] }}</td>
                                            <td><code>{{ $row['generated_password'] }}</code></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card admin-card h-100">
                <div class="card-body">
                    <h5 class="card-title">Errors</h5>
                    @if (empty($importErrors))
                        <div class="text-muted">No errors found.</div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($importErrors as $error)
                                <div class="list-group-item px-0">
                                    <div class="fw-semibold">Row {{ $error['row'] }}</div>
                                    <ul class="mb-0">
                                        @foreach ($error['messages'] as $message)
                                            <li>{{ $message }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
