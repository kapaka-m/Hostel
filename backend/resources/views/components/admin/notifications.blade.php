@php
    $userId = auth()->id();

    $baseQuery = \App\Models\AdminNotification::query()
        ->when($userId, fn($q) => $q->where('user_id', $userId))
        ->orderByDesc('created_at');

    $notifications = $userId ? (clone $baseQuery)->limit(7)->get() : collect();
    $count = $userId ? (clone $baseQuery)->whereNull('read_at')->count() : 0;
@endphp

<div class="dropdown">
    <button class="btn btn-icon btn-outline-secondary btn-sm position-relative" type="button" data-bs-toggle="dropdown"
        data-bs-auto-close="outside" aria-expanded="false" aria-label="Notifications" title="Notifications">
        <i class="bi bi-bell"></i>

        @if ($count > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $count }}
            </span>
        @endif
    </button>


    <div class="dropdown-menu dropdown-menu-end shadow-lg p-0" style="width: 360px; max-width: 92vw;">
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
            <div class="fw-semibold">Notifications</div>
            @if ($count > 0)
                <span class="badge text-bg-danger">{{ $count }} unread</span>
            @endif
        </div>

        <div class="p-2" style="max-height: 380px; overflow: auto;">
            @forelse ($notifications as $notification)
                @php $isUnread = is_null($notification->read_at); @endphp
                <div class="border rounded-3 p-2 mb-2 {{ $isUnread ? 'feed-new' : '' }}">
                    <div class="d-flex justify-content-between gap-2">
                        <div class="fw-semibold">
                            @if ($notification->link)
                                <a href="{{ $notification->link }}" class="text-decoration-none">
                                    {{ $notification->title }}
                                </a>
                            @else
                                {{ $notification->title }}
                            @endif
                        </div>
                        <div class="small text-muted">
                            {{ $notification->created_at?->diffForHumans() }}
                        </div>
                    </div>

                    @if ($notification->body)
                        <div class="small text-muted mt-1">{{ $notification->body }}</div>
                    @endif

                    @if ($isUnread)
                        <div class="small mt-1">
                            <span class="badge text-bg-info">New</span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="small text-muted px-2 py-3">No notifications yet.</div>
            @endforelse
        </div>

        @if ($notifications->isNotEmpty() && $count > 0)
            <div class="p-2 border-top">
                <form method="POST" action="{{ route('admin.notifications.read') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                        Mark all as read
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
