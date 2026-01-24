@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Activity Feed</h2>
        <a href="{{ route('admin.university.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0 text-muted">Latest 100 events</h6>
                <span class="badge bg-secondary" id="feed-status">Live</span>
            </div>

            <div id="activity-feed" class="list-group small">
                @forelse ($logs as $log)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $log->action }}</strong>
                                <div class="text-muted">
                                    {{ $log->entity_type }}{{ $log->entity_id ? ' #' . $log->entity_id : '' }}
                                    @if($log->actor)
                                        &middot; {{ $log->actor->name }} ({{ $log->actor->email }})
                                    @endif
                                </div>
                            </div>
                            <div class="text-muted">{{ optional($log->created_at)->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-muted">No activity yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        const feedEndpoint = "{{ route('admin.university.activity-feed.data') }}";
        const feedContainer = document.getElementById('activity-feed');
        const statusBadge = document.getElementById('feed-status');

        const renderItem = (item) => {
            const actor = item.actor ? ` &middot; ${item.actor.name} (${item.actor.email})` : '';
            const entity = item.entity_id ? ` #${item.entity_id}` : '';
            const createdAt = item.created_at ? new Date(item.created_at).toLocaleString() : '';

            return `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${item.action}</strong>
                            <div class="text-muted">${item.entity_type}${entity}${actor}</div>
                        </div>
                        <div class="text-muted">${createdAt}</div>
                    </div>
                </div>
            `;
        };

        const refreshFeed = async () => {
            try {
                const response = await fetch(feedEndpoint, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) {
                    statusBadge.textContent = 'Offline';
                    statusBadge.className = 'badge bg-danger';
                    return;
                }

                const payload = await response.json();
                const items = payload.data ?? [];
                feedContainer.innerHTML = items.length
                    ? items.map(renderItem).join('')
                    : '<div class="list-group-item text-muted">No activity yet.</div>';

                statusBadge.textContent = 'Live';
                statusBadge.className = 'badge bg-secondary';
            } catch (error) {
                statusBadge.textContent = 'Offline';
                statusBadge.className = 'badge bg-danger';
            }
        };

        refreshFeed();
        setInterval(refreshFeed, 5000);
    </script>
@endsection
