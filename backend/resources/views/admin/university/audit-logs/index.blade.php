@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Audit Logs</h2>
        <a href="{{ route('admin.university.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                <tr>
                    <th scope="col">Time</th>
                    <th scope="col">Action</th>
                    <th scope="col">Entity</th>
                    <th scope="col">Actor</th>
                    <th scope="col">Correlation</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="text-muted">{{ optional($log->created_at)->toDateTimeString() }}</td>
                        <td>{{ $log->action }}</td>
                        <td>
                            {{ $log->entity_type }}
                            @if($log->entity_id)
                                #{{ $log->entity_id }}
                            @endif
                        </td>
                        <td>
                            @if($log->actor)
                                {{ $log->actor->name }} ({{ $log->actor->email }})
                            @else
                                <span class="text-muted">System</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $log->correlation_id }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No audit entries found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>
@endsection
