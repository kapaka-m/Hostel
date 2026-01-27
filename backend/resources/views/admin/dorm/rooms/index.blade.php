@extends('layouts.app')

@section('title', 'Rooms')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h2 class="mb-1">Rooms</h2>
            <div class="text-muted">Visualize occupancy, capacity, and availability.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.dorm.rooms.export', request()->query()) }}" class="btn btn-outline-secondary">Export
                CSV</a>
            <a href="{{ route('admin.dorm.rooms.create') }}" class="btn btn-primary">Add Room</a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.dorm.rooms.index') }}" class="row g-2 mb-3">
        <div class="col-md-3">
            <input type="search" name="q" class="form-control" placeholder="Search by room number"
                value="{{ $filters['q'] }}">
        </div>
        <div class="col-md-3">
            <select name="floor_id" class="form-select">
                <option value="">All floors</option>
                @foreach ($floors as $floor)
                    <option value="{{ $floor->id }}" @selected((string) $filters['floor_id'] === (string) $floor->id)>
                        Floor {{ $floor->number }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                <option value="AVAILABLE" @selected($filters['status'] === 'AVAILABLE')>Available</option>
                <option value="PARTIAL" @selected($filters['status'] === 'PARTIAL')>Partially occupied</option>
                <option value="FULL" @selected($filters['status'] === 'FULL')>Full</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="availability" class="form-select">
                <option value="">Availability</option>
                <option value="has_space" @selected($filters['availability'] === 'has_space')>Has space</option>
                <option value="full" @selected($filters['availability'] === 'full')>Full only</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="visibility" class="form-select">
                <option value="">Active</option>
                <option value="archived" @selected($filters['visibility'] === 'archived')>Archived</option>
            </select>
        </div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-secondary" type="submit">Apply</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.dorm.rooms.index') }}">Reset</a>
        </div>
    </form>

    <form id="bulk-rooms-form" method="POST">
        @csrf
    </form>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            <input type="checkbox" class="form-check-input" id="bulk-rooms-select-all">
            <label for="bulk-rooms-select-all" class="form-check-label">Select all</label>
            <span class="text-muted small" id="bulk-rooms-count">0 selected</span>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <select id="bulk-rooms-action" class="form-select form-select-sm" style="min-width: 200px;">
                <option value="">Bulk actions</option>
                <option value="{{ route('admin.dorm.rooms.bulk-delete') }}">Archive selected</option>
                <option value="{{ route('admin.dorm.rooms.bulk-restore') }}">Restore selected</option>
                <option value="{{ route('admin.dorm.rooms.bulk-export') }}">Export selected</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary" type="submit" form="bulk-rooms-form" id="bulk-rooms-submit"
                disabled>Apply</button>
        </div>
    </div>

    <div class="row g-3">
        @forelse ($rooms as $room)
            @php
                $assigned = $room->active_assignments_count;
                $capacity = max((int) $room->capacity, 1);
                $occupancy = min(100, (int) round(($assigned / $capacity) * 100));
                $available = max($room->capacity - $assigned, 0);
                $statusLabel = $room->trashed() ? 'ARCHIVED' : $room->status;
                $statusColor = $room->trashed()
                    ? 'secondary'
                    : match ($room->status) {
                        'FULL' => 'danger',
                        'PARTIAL' => 'warning',
                        default => 'success',
                    };
            @endphp
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="d-flex gap-2">
                                <div class="form-check mt-1">
                                    <input class="form-check-input bulk-room-select" type="checkbox" name="ids[]"
                                        value="{{ $room->id }}" form="bulk-rooms-form">
                                </div>
                                <div>
                                    <div class="text-muted small">Room {{ $room->room_number }}</div>
                                    <h5 class="mb-0">Floor {{ $room->floor?->number ?? 'N/A' }}</h5>
                                </div>
                            </div>
                            <span class="admin-pill bg-{{ $statusColor }} text-white">{{ $statusLabel }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">Occupancy</div>
                            <div class="fw-semibold">{{ $assigned }}/{{ $room->capacity }}</div>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-{{ $statusColor }}" style="width: {{ $occupancy }}%"></div>
                        </div>
                        <div class="text-muted small">{{ $available }} available beds</div>
                        <div class="d-flex flex-wrap gap-2 mt-auto">
                            @if (!$room->trashed())
                                <a href="{{ route('admin.dorm.rooms.show', $room) }}"
                                    class="btn btn-sm btn-outline-primary">Details</a>
                                <a href="{{ route('admin.dorm.rooms.edit', $room) }}"
                                    class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form method="POST" action="{{ route('admin.dorm.rooms.destroy', $room) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger js-confirm" type="submit"
                                        data-confirm="Archive this room?">Archive</button>

                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.dorm.rooms.restore', $room) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success" type="submit">Restore</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-4">No rooms found.</div>
            </div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $rooms->links('pagination::bootstrap-5') }}
    </div>
@endsection

@push('scripts')
    <script>
        const bulkRoomsForm = document.getElementById('bulk-rooms-form');
        const bulkRoomsSelectAll = document.getElementById('bulk-rooms-select-all');
        const bulkRoomsAction = document.getElementById('bulk-rooms-action');
        const bulkRoomsSubmit = document.getElementById('bulk-rooms-submit');
        const bulkRoomsCount = document.getElementById('bulk-rooms-count');
        const bulkRoomsItems = document.querySelectorAll('.bulk-room-select');

        const updateBulkRoomsState = () => {
            const selected = Array.from(bulkRoomsItems).filter((checkbox) => checkbox.checked);
            const hasAction = bulkRoomsAction && bulkRoomsAction.value;
            if (bulkRoomsCount) {
                bulkRoomsCount.textContent = `${selected.length} selected`;
            }
            if (bulkRoomsSubmit) {
                bulkRoomsSubmit.disabled = selected.length === 0 || !hasAction;
            }
        };

        if (bulkRoomsSelectAll) {
            bulkRoomsSelectAll.addEventListener('change', () => {
                bulkRoomsItems.forEach((checkbox) => {
                    checkbox.checked = bulkRoomsSelectAll.checked;
                });
                updateBulkRoomsState();
            });
        }

        bulkRoomsItems.forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                if (bulkRoomsSelectAll && !checkbox.checked) {
                    bulkRoomsSelectAll.checked = false;
                }
                updateBulkRoomsState();
            });
        });

        if (bulkRoomsAction) {
            bulkRoomsAction.addEventListener('change', updateBulkRoomsState);
        }

        if (bulkRoomsForm) {
            bulkRoomsForm.addEventListener('submit', (event) => {
                const action = bulkRoomsAction ? bulkRoomsAction.value : '';
                if (!action) {
                    event.preventDefault();
                    return;
                }
                bulkRoomsForm.action = action;
                if (action.includes('bulk-delete')) {
                    event.preventDefault();

                    const modalEl = document.getElementById('confirmModal');
                    const textEl = document.getElementById('confirmModalText');
                    const yesBtn = document.getElementById('confirmModalYes');

                    if (!modalEl || !window.bootstrap || !textEl || !yesBtn) {
                        if (confirm('Archive selected rooms?')) bulkRoomsForm.submit();
                        return;
                    }

                    textEl.textContent = 'Archive selected rooms?';

                    const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);

                    const handler = () => {
                        yesBtn.removeEventListener('click', handler);
                        modal.hide();
                        bulkRoomsForm.submit();
                    };

                    yesBtn.addEventListener('click', handler);
                    modal.show();
                }

            });
        }

        updateBulkRoomsState();
    </script>
@endpush
