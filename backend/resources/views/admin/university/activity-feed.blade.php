@extends('layouts.app')

@section('title', 'Activity Feed')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1">Activity Feed</h2>
            <div class="text-muted">Latest 100 events across the university.</div>
        </div>
        <a href="{{ route('admin.university.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
    </div>

    <div class="card admin-card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary" id="feed-status">Live</span>
                    <span class="text-muted small">Auto-refresh every 5s</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <input type="search" id="feed-filter" class="form-control form-control-sm"
                        placeholder="Filter action or actor" value="{{ request('q') }}" style="min-width: 220px;">
                    <button class="btn btn-sm btn-outline-secondary" id="feed-toggle" type="button">Pause</button>
                </div>
            </div>

            <div id="activity-feed" class="list-group small">
                @forelse ($logs as $log)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $log->action }}</strong>
                                <div class="text-muted">
                                    {{ $log->entity_type }}{{ $log->entity_id ? ' #' . $log->entity_id : '' }}
                                    @if ($log->actor)
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
        const baseEndpoint = "{{ route('admin.university.activity-feed.data') }}";
        const feedContainer = document.getElementById('activity-feed');
        const statusBadge = document.getElementById('feed-status');
        const toggleButton = document.getElementById('feed-toggle');
        const filterInput = document.getElementById('feed-filter');

        let paused = false;
        let lastSeenId = null;
        let refreshTimer = null;

        const renderItem = (item, isNew) => {
            const actor = item.actor ? ` &middot; ${item.actor.name} (${item.actor.email})` : '';
            const entity = item.entity_id ? ` #${item.entity_id}` : '';
            const createdAt = item.created_at ? new Date(item.created_at).toLocaleString() : '';
            const highlightClass = isNew ? 'feed-new' : '';

            return `
                <div class="list-group-item ${highlightClass}">
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

        const buildEndpoint = () => {
            const term = filterInput.value.trim();
            if (!term) {
                return baseEndpoint;
            }
            const params = new URLSearchParams({
                q: term
            });
            return `${baseEndpoint}?${params.toString()}`;
        };

        const refreshFeed = async () => {
            if (paused) {
                return;
            }

            try {
                const response = await fetch(buildEndpoint(), {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!response.ok) {
                    statusBadge.textContent = 'Offline';
                    statusBadge.className = 'badge bg-danger';
                    return;
                }

                const payload = await response.json();
                const items = payload.data ?? [];

                const newestId = items.length ? items[0].id : null;
                feedContainer.innerHTML = items.length ?
                    items.map((item) => renderItem(item, lastSeenId && item.id > lastSeenId)).join('') :
                    '<div class="list-group-item text-muted">No activity yet.</div>';

                if (newestId) {
                    lastSeenId = newestId;
                }

                statusBadge.textContent = 'Live';
                statusBadge.className = 'badge bg-secondary';
            } catch (error) {
                statusBadge.textContent = 'Offline';
                statusBadge.className = 'badge bg-danger';
            }
        };

        const toggleRefresh = () => {
            paused = !paused;
            toggleButton.textContent = paused ? 'Resume' : 'Pause';
            statusBadge.textContent = paused ? 'Paused' : 'Live';
            statusBadge.className = paused ? 'badge bg-warning' : 'badge bg-secondary';
        };

        toggleButton.addEventListener('click', toggleRefresh);
        filterInput.addEventListener('input', () => {
            clearTimeout(refreshTimer);
            refreshTimer = setTimeout(refreshFeed, 400);
        });

        refreshFeed();
        setInterval(refreshFeed, 5000);
    </script>
@endsection
