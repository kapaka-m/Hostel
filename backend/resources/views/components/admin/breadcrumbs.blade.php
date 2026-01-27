@php
    use Illuminate\Support\Str;

    $routeName = optional(request()->route())->getName();
    $crumbs = [];
    $labelMap = [
        'admin' => 'Admin',
        'university' => 'University',
        'dorm' => 'Dorm',
        'super' => 'System',
        'dashboard' => 'Dashboard',
        'dorms' => 'Dorms',
        'dorm-admins' => 'Dorm Admins',
        'floors' => 'Floors',
        'rooms' => 'Rooms',
        'students' => 'Students',
        'assignments' => 'Assignments',
        'activity-feed' => 'Activity Feed',
        'audit-logs' => 'Audit Logs',
        'tickets' => 'Tickets',
        'announcements' => 'Announcements',
        'reports' => 'Reports',
        'settings' => 'Settings',
        'create' => 'Create',
        'edit' => 'Edit',
        'show' => 'Details',
        'index' => 'List',
    ];

    if ($routeName && str_starts_with($routeName, 'admin.')) {
        $segments = explode('.', $routeName);
        $path = [];

        foreach ($segments as $segment) {
            $path[] = $segment;
            $label = $labelMap[$segment] ?? Str::title(str_replace('-', ' ', $segment));
            $routeKey = implode('.', $path);

            if ($routeKey === 'admin' && Route::has('admin.home')) {
                $routeKey = 'admin.home';
            }

            $crumbs[] = [
                'label' => $label,
                'route' => $routeKey,
            ];
        }
    }
@endphp

@if ($crumbs)
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb admin-breadcrumbs mb-0">
            @foreach ($crumbs as $index => $crumb)
                @php
                    $isLast = $index === count($crumbs) - 1;
                    $routeExists = Route::has($crumb['route']);
                @endphp
                @if ($isLast || !$routeExists)
                    <li class="breadcrumb-item {{ $isLast ? 'active' : '' }}"
                        @if ($isLast) aria-current="page" @endif>
                        {{ $crumb['label'] }}
                    </li>
                @else
                    <li class="breadcrumb-item">
                        <a href="{{ route($crumb['route']) }}">{{ $crumb['label'] }}</a>
                    </li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif
