@extends('layouts.app')

@section('title', 'Floors')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h2 class="mb-1">Floors</h2>
            <div class="text-muted">Organize your dorm by floor and facility counts.</div>
        </div>
        <a href="{{ route('admin.dorm.floors.create') }}" class="btn btn-primary">Add Floor</a>
    </div>

    <form class="row g-2 mb-3" method="GET" action="{{ route('admin.dorm.floors.index') }}">
        <div class="col-md-4">
            <input type="search" name="q" class="form-control" placeholder="Search by number"
                value="{{ $filters['q'] }}">
        </div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-secondary" type="submit">Apply</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.dorm.floors.index') }}">Reset</a>
        </div>
    </form>

    <form id="bulk-delete-form" method="POST" action="{{ route('admin.dorm.floors.bulk-delete') }}">

        @csrf
    </form>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <div class="text-muted">Select floors to delete in bulk.</div>
        <button class="btn btn-sm btn-outline-danger" type="submit" form="bulk-delete-form" id="bulk-delete-button"
            disabled>Delete selected</button>
    </div>

    <div class="admin-table">
        <table class="table table-striped align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 36px;">
                        <input type="checkbox" class="form-check-input" id="bulk-select-all">
                    </th>
                    <th>Number</th>
                    <th>Rooms</th>
                    <th>Bathrooms</th>
                    <th>Kitchens</th>
                    <th>Showers</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($floors as $floor)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input bulk-select" name="ids[]"
                                value="{{ $floor->id }}" form="bulk-delete-form">
                        </td>
                        <td class="fw-semibold">{{ $floor->number }}</td>
                        <td>{{ $floor->rooms_count }}</td>
                        <td>{{ $floor->bathrooms }}</td>
                        <td>{{ $floor->kitchens }}</td>
                        <td>{{ $floor->showers }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.dorm.floors.edit', $floor) }}"
                                class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="{{ route('admin.dorm.floors.destroy', $floor) }}"
                                class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger js-confirm" type="submit"
                                    data-confirm="Delete this floor?">Delete</button>

                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">No floors found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $floors->links('pagination::bootstrap-5') }}
    </div>
@endsection

@push('scripts')
    <script>
        const selectAll = document.getElementById('bulk-select-all');
        const checkboxes = document.querySelectorAll('.bulk-select');
        const bulkButton = document.getElementById('bulk-delete-button');

        const updateBulkButton = () => {
            const anyChecked = Array.from(checkboxes).some((checkbox) => checkbox.checked);
            bulkButton.disabled = !anyChecked;
        };

        if (selectAll) {
            selectAll.addEventListener('change', () => {
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = selectAll.checked;
                });
                updateBulkButton();
            });
        }

        checkboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                if (selectAll && !checkbox.checked) {
                    selectAll.checked = false;
                }
                updateBulkButton();
            });
        });

        if (checkboxes.length === 0 && selectAll) {
            selectAll.disabled = true;
        }

        updateBulkButton();

        const bulkForm = document.getElementById('bulk-delete-form');

        if (bulkForm) {
            bulkForm.addEventListener('submit', (event) => {
                const anyChecked = Array.from(checkboxes).some((checkbox) => checkbox.checked);
                if (!anyChecked) {
                    event.preventDefault();
                    return;
                }

                event.preventDefault();

                const modalEl = document.getElementById('confirmModal');
                const textEl = document.getElementById('confirmModalText');
                const yesBtn = document.getElementById('confirmModalYes');

                if (!modalEl || !window.bootstrap || !textEl || !yesBtn) {
                    if (confirm('Delete selected floors?')) bulkForm.submit();
                    return;
                }

                textEl.textContent = 'Delete selected floors?';

                const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);

                const handler = () => {
                    yesBtn.removeEventListener('click', handler);
                    modal.hide();
                    bulkForm.submit();
                };

                yesBtn.addEventListener('click', handler);
                modal.show();
            });
        }
    </script>
@endpush
