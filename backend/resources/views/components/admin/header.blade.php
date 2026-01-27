@php
    $user = auth()->user();
@endphp

<header class="admin-header py-3">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#adminSidebar" aria-controls="adminSidebar">
                    Menu
                </button>

                <div class="d-none d-lg-block">
                    <span class="fw-semibold">Welcome back, {{ $user?->name ?? 'Admin' }}</span>
                    <div class="small text-muted">{{ now()->format('l, M j') }}</div>
                </div>
            </div>

            <div class="flex-grow-1 d-none d-xl-block">
                @if (Route::has('admin.search'))
                    <form method="GET" action="{{ route('admin.search') }}" class="position-relative">
                        <i class="bi bi-search search-icon"></i>
                        <input type="search" name="q" class="form-control search-input"
                            placeholder="Search dorms, rooms, students" value="{{ request('q') }}">
                    </form>
                @endif
            </div>

            <div class="d-flex align-items-center gap-2">
                {{-- Theme icon button --}}
                <button type="button" class="btn btn-icon btn-outline-secondary btn-sm" data-theme-toggle
                    data-theme-endpoint="{{ Route::has('admin.preferences.theme') ? route('admin.preferences.theme') : '' }}"
                    data-theme-value="{{ $user?->ui_theme ?? 'light' }}" aria-label="Toggle theme" title="Toggle theme">
                    <i class="bi" data-theme-icon></i>
                </button>


                {{-- Notifications icon component --}}
                <x-admin.notifications />

                {{-- Logout red --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-danger btn-sm" type="submit">
                        <i class="bi bi-box-arrow-right me-1"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
