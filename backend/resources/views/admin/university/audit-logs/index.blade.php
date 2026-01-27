@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1">Audit Logs</h2>
            <div class="text-muted">Filter by actor, entity, and correlation id.</div>
        </div>
        <a href="{{ route('admin.university.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
    </div>

    <form class="row g-2 mb-3" method="GET" action="{{ route('admin.university.audit-logs.index') }}">
        <div class="col-md-3">
            <input type="text" name="action" class="form-control" placeholder="Action" value="{{ $filters['action'] }}">
        </div>
        <div class="col-md-2">
            <input type="text" name="entity_type" class="form-control" placeholder="Entity Type"
                value="{{ $filters['entity_type'] }}">
        </div>
        <div class="col-md-2">
            <input type="text" name="entity_id" class="form-control" placeholder="Entity ID"
                value="{{ $filters['entity_id'] }}">
        </div>
        <div class="col-md-2">
            <input type="text" name="actor" class="form-control" placeholder="Actor" value="{{ $filters['actor'] }}">
        </div>
        <div class="col-md-3">
            <input type="text" name="correlation_id" class="form-control" placeholder="Correlation ID"
                value="{{ $filters['correlation_id'] }}">
        </div>
        <div class="col-md-2">
            <input type="date" name="from" class="form-control" value="{{ $filters['from'] }}">
        </div>
        <div class="col-md-2">
            <input type="date" name="to" class="form-control" value="{{ $filters['to'] }}">
        </div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-secondary" type="submit">Apply</button>
            <a href="{{ route('admin.university.audit-logs.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="card admin-card">
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
                                @if ($log->entity_id)
                                    #{{ $log->entity_id }}
                                @endif
                            </td>
                            <td>
                                @if ($log->actor)
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
