@extends('layouts.app')

@section('title', 'Students')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h2 class="mb-1">Students</h2>
            <div class="text-muted">Track assignments, contacts, and status.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.dorm.students.export', request()->query()) }}" class="btn btn-outline-secondary">Export
                CSV</a>
            <a href="{{ route('admin.dorm.students.create') }}" class="btn btn-primary">Add Student</a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.dorm.students.index') }}" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="search" name="q" class="form-control" placeholder="Search by name, student no, email"
                value="{{ $filters['q'] }}">
        </div>
        <div class="col-md-3">
            <select name="assignment" class="form-select">
                <option value="">All assignments</option>
                <option value="assigned" @selected($filters['assignment'] === 'assigned')>Assigned</option>
                <option value="unassigned" @selected($filters['assignment'] === 'unassigned')>Unassigned</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Active</option>
                <option value="archived" @selected($filters['status'] === 'archived')>Archived</option>
            </select>
        </div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-secondary" type="submit">Apply</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.dorm.students.index') }}">Reset</a>
        </div>
    </form>

    <form id="bulk-students-form" method="POST">
        @csrf
    </form>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            <input type="checkbox" class="form-check-input" id="bulk-students-select-all">
            <label for="bulk-students-select-all" class="form-check-label">Select all</label>
            <span class="text-muted small" id="bulk-students-count">0 selected</span>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <select id="bulk-students-action" class="form-select form-select-sm" style="min-width: 210px;">
                <option value="">Bulk actions</option>
                <option value="{{ route('admin.dorm.students.bulk-delete') }}">Archive selected</option>
                <option value="{{ route('admin.dorm.students.bulk-restore') }}">Restore selected</option>
                <option value="{{ route('admin.dorm.students.bulk-freeze') }}">Freeze selected</option>
                <option value="{{ route('admin.dorm.students.bulk-unfreeze') }}">Unfreeze selected</option>
                <option value="{{ route('admin.dorm.students.bulk-export') }}">Export selected</option>
            </select>
            <input type="text" name="reason" id="bulk-students-reason" class="form-control form-control-sm"
                placeholder="Reason (optional)" form="bulk-students-form" style="min-width: 200px; display: none;">
            <button class="btn btn-sm btn-outline-secondary" type="submit" form="bulk-students-form"
                id="bulk-students-submit" disabled>Apply</button>
        </div>
    </div>

    <div class="admin-table">
        <table class="table table-striped align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 36px;">
                        <input type="checkbox" class="form-check-input" id="bulk-students-select-all-header">
                    </th>
                    <th>Student</th>
                    <th>Student No</th>
                    <th>Email</th>
                    <th>Room</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input bulk-student-select" name="ids[]"
                                value="{{ $student->id }}" form="bulk-students-form">
                        </td>
                        <td class="fw-semibold">{{ $student->full_name }}</td>
                        <td>{{ $student->student_no }}</td>
                        <td>{{ $student->user?->email }}</td>
                        <td>
                            @if ($student->activeAssignment?->room)
                                Room {{ $student->activeAssignment->room->room_number }}
                            @else
                                <span class="text-muted">Unassigned</span>
                            @endif
                        </td>
                        <td>
                            @if ($student->trashed())
                                <span class="admin-pill bg-secondary text-white">Archived</span>
                            @else
                                <span class="admin-pill bg-success text-white">Active</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.dorm.students.show', $student) }}"
                                class="btn btn-sm btn-outline-primary">Details</a>
                            @if (!$student->trashed())
                                <a href="{{ route('admin.dorm.students.edit', $student) }}"
                                    class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form method="POST" action="{{ route('admin.dorm.students.destroy', $student) }}"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger js-confirm" type="submit"
                                        data-confirm="Archive this student?">Archive</button>

                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.dorm.students.restore', $student) }}"
                                    class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success" type="submit">Restore</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">No students found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $students->links('pagination::bootstrap-5') }}
    </div>
@endsection

@push('scripts')
    <script>
        const bulkStudentsForm = document.getElementById('bulk-students-form');
        const bulkStudentsSelectAll = document.getElementById('bulk-students-select-all');
        const bulkStudentsSelectAllHeader = document.getElementById('bulk-students-select-all-header');
        const bulkStudentsAction = document.getElementById('bulk-students-action');
        const bulkStudentsSubmit = document.getElementById('bulk-students-submit');
        const bulkStudentsReason = document.getElementById('bulk-students-reason');
        const bulkStudentsCount = document.getElementById('bulk-students-count');
        const bulkStudentsItems = document.querySelectorAll('.bulk-student-select');

        const updateBulkStudentsState = () => {
            const selected = Array.from(bulkStudentsItems).filter((checkbox) => checkbox.checked);
            const hasAction = bulkStudentsAction && bulkStudentsAction.value;
            if (bulkStudentsCount) {
                bulkStudentsCount.textContent = `${selected.length} selected`;
            }
            if (bulkStudentsSubmit) {
                bulkStudentsSubmit.disabled = selected.length === 0 || !hasAction;
            }
            if (bulkStudentsReason) {
                const action = bulkStudentsAction ? bulkStudentsAction.value : '';
                const needsReason = action.includes('bulk-freeze') || action.includes('bulk-unfreeze');
                bulkStudentsReason.style.display = needsReason ? 'block' : 'none';
            }
        };

        const syncSelectAll = (checked) => {
            bulkStudentsItems.forEach((checkbox) => {
                checkbox.checked = checked;
            });
            updateBulkStudentsState();
        };

        if (bulkStudentsSelectAll) {
            bulkStudentsSelectAll.addEventListener('change', () => syncSelectAll(bulkStudentsSelectAll.checked));
        }

        if (bulkStudentsSelectAllHeader) {
            bulkStudentsSelectAllHeader.addEventListener('change', () => syncSelectAll(bulkStudentsSelectAllHeader
                .checked));
        }

        bulkStudentsItems.forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                if ((bulkStudentsSelectAll && !checkbox.checked) || (bulkStudentsSelectAllHeader && !
                        checkbox.checked)) {
                    if (bulkStudentsSelectAll) {
                        bulkStudentsSelectAll.checked = false;
                    }
                    if (bulkStudentsSelectAllHeader) {
                        bulkStudentsSelectAllHeader.checked = false;
                    }
                }
                updateBulkStudentsState();
            });
        });

        if (bulkStudentsAction) {
            bulkStudentsAction.addEventListener('change', updateBulkStudentsState);
        }

        if (bulkStudentsForm) {
            bulkStudentsForm.addEventListener('submit', (event) => {
                const action = bulkStudentsAction ? bulkStudentsAction.value : '';
                if (!action) {
                    event.preventDefault();
                    return;
                }
                bulkStudentsForm.action = action;
                if (action.includes('bulk-delete')) {
                    event.preventDefault();

                    const modalEl = document.getElementById('confirmModal');
                    const textEl = document.getElementById('confirmModalText');
                    const yesBtn = document.getElementById('confirmModalYes');

                    if (!modalEl || !window.bootstrap || !textEl || !yesBtn) {
                        // fallback
                        if (confirm('Archive selected students?')) bulkStudentsForm.submit();
                        return;
                    }

                    textEl.textContent = 'Archive selected students?';

                    const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);

                    const handler = () => {
                        yesBtn.removeEventListener('click', handler);
                        modal.hide();
                        bulkStudentsForm.submit();
                    };

                    yesBtn.addEventListener('click', handler);
                    modal.show();
                }

            });
        }

        updateBulkStudentsState();
    </script>
@endpush
