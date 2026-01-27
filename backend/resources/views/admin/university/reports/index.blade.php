@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    @php
        $trendValues = $trend['values'];
        $trendMax = max($trendValues) ?: 1;
        $chartWidth = 600;
        $chartHeight = 120;
        $pointCount = count($trendValues);
        $points = [];
        foreach ($trendValues as $index => $value) {
            $x = $pointCount > 1 ? $index * ($chartWidth / ($pointCount - 1)) : 0;
            $y = $chartHeight - ($value / $trendMax) * $chartHeight;
            $points[] = $x . ',' . $y;
        }
        $ticketMax = (int) (max($ticketStatusCounts->values()->all() ?: [1]) ?: 1);
    @endphp

    <div class="mb-3">
        <h2 class="mb-1">Reports</h2>
        <div class="text-muted">University occupancy and operational signals.</div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Dorms</div>
                    <div class="fs-4 fw-semibold">{{ $totals['dorms'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Rooms</div>
                    <div class="fs-4 fw-semibold">{{ $totals['rooms'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Students</div>
                    <div class="fs-4 fw-semibold">{{ $totals['students'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Occupancy</div>
                    <div class="fs-4 fw-semibold">{{ $totals['occupied'] }}/{{ $totals['capacity'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Assignments Trend (14 days)</h5>
                    <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" class="w-100" preserveAspectRatio="none"
                        style="min-height: 140px;">
                        <polyline fill="none" stroke="var(--admin-accent)" stroke-width="3"
                            points="{{ implode(' ', $points) }}" />
                    </svg>
                    <div class="d-flex justify-content-between text-muted small mt-2">
                        <span>{{ $trend['labels'][0] }}</span>
                        <span>{{ $trend['labels'][count($trend['labels']) - 1] }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Tickets by Status (30 days)</h5>
                    @foreach (\App\Models\Ticket::STATUSES as $status)
                        @php
                            $count = (int) ($ticketStatusCounts[$status] ?? 0);
                            $percent = $ticketMax > 0 ? (int) round(($count / $ticketMax) * 100) : 0;
                        @endphp
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small">
                                <span>{{ ucfirst(strtolower(str_replace('_', ' ', $status))) }}</span>
                                <span>{{ $count }}</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5 class="card-title">Dorm Occupancy</h5>
            <div class="admin-table">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Dorm</th>
                            <th>Capacity</th>
                            <th>Occupied</th>
                            <th>Percent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dorms as $dorm)
                            <tr>
                                <td class="fw-semibold">{{ $dorm['name'] }}</td>
                                <td>{{ $dorm['capacity'] }}</td>
                                <td>{{ $dorm['occupied'] }}</td>
                                <td>{{ $dorm['percent'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">No dorm data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
