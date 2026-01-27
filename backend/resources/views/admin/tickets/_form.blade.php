@php
    $priorities = \App\Models\Ticket::PRIORITIES;
    $statuses = \App\Models\Ticket::STATUSES;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Subject</label>
        <input type="text" name="subject" class="form-control" value="{{ old('subject', $ticket->subject) }}" required>
        @error('subject')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Priority</label>
        <select name="priority" class="form-select">
            @foreach ($priorities as $priority)
                <option value="{{ $priority }}" @selected(old('priority', $ticket->priority ?? 'MEDIUM') === $priority)>
                    {{ ucfirst(strtolower($priority)) }}
                </option>
            @endforeach
        </select>
        @error('priority')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Category</label>
        <input type="text" name="category" class="form-control" value="{{ old('category', $ticket->category) }}">
        @error('category')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    @if (!empty($dorms) && $dorms->isNotEmpty())
        <div class="col-md-6">
            <label class="form-label">Dorm (optional)</label>
            <select name="dorm_id" class="form-select">
                <option value="">All dorms</option>
                @foreach ($dorms as $dorm)
                    <option value="{{ $dorm->id }}" @selected((string) old('dorm_id', $ticket->dorm_id) === (string) $dorm->id)>
                        {{ $dorm->name }}
                    </option>
                @endforeach
            </select>
            @error('dorm_id')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    @endif
    <div class="col-md-6">
        <label class="form-label">Assigned To</label>
        <select name="assigned_to" class="form-select">
            <option value="">Unassigned</option>
            @foreach ($assignees as $assignee)
                <option value="{{ $assignee->id }}" @selected((string) old('assigned_to', $ticket->assigned_to) === (string) $assignee->id)>
                    {{ $assignee->name }} ({{ $assignee->role }})
                </option>
            @endforeach
        </select>
        @error('assigned_to')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    @if ($ticket->exists)
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $ticket->status) === $status)>
                        {{ ucfirst(strtolower(str_replace('_', ' ', $status))) }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    @endif
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="6" class="form-control" required>{{ old('description', $ticket->description) }}</textarea>
        @error('description')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
</div>
