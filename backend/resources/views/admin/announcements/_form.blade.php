@php
    $statusOptions = ['DRAFT' => 'Draft', 'PUBLISHED' => 'Publish'];
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $announcement->title) }}" required>
        @error('title')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    @if (!empty($showAudience) && $showAudience)
        <div class="col-md-4">
            <label class="form-label">Audience</label>
            <select name="audience" class="form-select">
                @foreach (\App\Models\Announcement::AUDIENCES as $audience)
                    <option value="{{ $audience }}" @selected(old('audience', $announcement->audience ?? 'UNIVERSITY') === $audience)>
                        {{ ucfirst(strtolower($audience)) }}
                    </option>
                @endforeach
            </select>
            @error('audience')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    @endif
    @if (!empty($dorms) && $dorms->isNotEmpty())
        <div class="col-md-6">
            <label class="form-label">Dorm (required for dorm audience)</label>
            <select name="dorm_id" class="form-select">
                <option value="">Select dorm</option>
                @foreach ($dorms as $dorm)
                    <option value="{{ $dorm->id }}" @selected((string) old('dorm_id', $announcement->dorm_id) === (string) $dorm->id)>
                        {{ $dorm->name }}
                    </option>
                @endforeach
            </select>
            @error('dorm_id')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    @endif
    <div class="col-md-3">
        <label class="form-label">Publish At</label>
        <input type="datetime-local" name="publish_at" class="form-control"
            value="{{ old('publish_at', optional($announcement->publish_at)->format('Y-m-d\\TH:i')) }}">
        @error('publish_at')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Expire At</label>
        <input type="datetime-local" name="expire_at" class="form-control"
            value="{{ old('expire_at', optional($announcement->expire_at)->format('Y-m-d\\TH:i')) }}">
        @error('expire_at')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach ($statusOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $announcement->status ?? 'DRAFT') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('status')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-12">
        <label class="form-label">Body</label>
        <textarea name="body" rows="6" class="form-control" required>{{ old('body', $announcement->body) }}</textarea>
        @error('body')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
</div>
