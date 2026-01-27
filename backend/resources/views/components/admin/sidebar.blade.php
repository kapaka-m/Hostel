@php
    use App\Support\AdminMenu;
    use Illuminate\Support\Str;

    $user = auth()->user();
    $sections = AdminMenu::sections($user);
@endphp

<aside class="admin-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="adminSidebar"
    aria-labelledby="adminSidebarLabel">
    <div class="offcanvas-header d-lg-none">
        <h5 class="offcanvas-title" id="adminSidebarLabel">Admin Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column gap-4 p-0">
        <div class="px-4 pt-4 text-center">
            <a class="brand text-white text-decoration-none d-inline-flex align-items-center justify-content-center"
                href="{{ Route::has('admin.home') ? route('admin.home') : '#' }}">
                <span class="login-badge2">
                    {{ config('logo', 'H') }}
                </span>
            </a>

            <div class="small text-white-50 mt-2">University Dorm Platform</div>
        </div>


        @foreach ($sections as $section)
            <div class="px-3">
                <div class="menu-section px-2 mb-2">{{ $section['label'] }}</div>
                <nav class="nav flex-column gap-1">
                    @foreach ($section['items'] as $item)
                        @php
                            $routeName = $item['route'];
                            $active = request()->routeIs($routeName);
                            if (!$active && Str::endsWith($routeName, '.index')) {
                                $active = request()->routeIs(Str::beforeLast($routeName, '.') . '.*');
                            }
                        @endphp
                        <a class="nav-link {{ $active ? 'active' : '' }}" href="{{ route($routeName) }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>
        @endforeach

        <div class="px-3 mt-auto pb-4">
            <div class="menu-section px-2 mb-2">Account</div>
            <div class="d-flex align-items-center gap-2 px-2">
                <div class="rounded-circle bg-light text-dark d-flex align-items-center justify-content-center"
                    style="width: 36px; height: 36px;">
                    {{ $user?->name ? strtoupper(substr($user->name, 0, 1)) : '?' }}
                </div>
                <div class="small">
                    <div class="text-white">{{ $user?->name }}</div>
                    <div class="text-white-50">{{ $user?->role }}</div>
                </div>
            </div>
        </div>
    </div>
</aside>
