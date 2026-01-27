@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
    <div class="mb-3">
        <h2 class="mb-1">System Settings</h2>
        <div class="text-muted">Manage feature flags and security defaults for your university.</div>
    </div>

    <form method="POST" action="{{ route('admin.university.settings.update') }}" class="d-flex flex-column gap-4">
        @csrf

        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Feature Flags</h5>
                <div class="row g-3">
                    @foreach ($featureFlags as $flag => $enabled)
                        @php
                            $label = ucwords(str_replace('_', ' ', $flag));
                        @endphp
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="flag_{{ $flag }}"
                                    name="{{ $flag }}" @checked($enabled)>
                                <label class="form-check-label" for="flag_{{ $flag }}">{{ $label }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="text-muted small mt-3">
                    Feature flags are cached for a short period. Changes may take up to a minute to apply.
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Admin IP Allowlist</h5>
                <div class="mb-3">
                    <label class="form-label">Allowed IPs (one per line)</label>
                    <textarea name="admin_ip_allowlist" rows="4" class="form-control">{{ implode(PHP_EOL, $adminIpAllowlist) }}</textarea>
                    @error('admin_ip_allowlist')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="include_current_ip"
                        name="include_current_ip" checked>
                    <label class="form-check-label" for="include_current_ip">
                        Include my current IP ({{ $currentIp }}) to prevent lockout
                    </label>
                </div>
                <div class="text-muted small mt-2">
                    Keep at least one trusted IP here before enabling the allowlist feature flag.
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Activity Retention</h5>
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Retention Days</label>
                        <input type="number" name="activity_retention_days" class="form-control" min="1"
                            value="{{ old('activity_retention_days', $activityRetentionDays) }}">
                        @error('activity_retention_days')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-8 text-muted small">
                        Older audit logs and activity feed records will be pruned using the retention command.
                    </div>
                </div>
            </div>
        </div>

        <div>
            <button class="btn btn-primary" type="submit">Save Settings</button>
        </div>
    </form>
@endsection
