<!doctype html>
<html lang="en" data-bs-theme="{{ auth()->user()?->ui_theme ?? 'light' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Hostel'))</title>
    @php
        $useVite = file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json'));
    @endphp
    @if ($useVite)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <style>
            body.admin-body {
                font-family: "Space Grotesk", "Segoe UI", sans-serif;
                background: #f4f6fb;
                color: #0f172a;
            }

            .admin-shell {
                min-height: 100vh;
                display: flex;
            }

            .admin-sidebar {
                width: 280px;
                background: #0f172a;
                color: #e2e8f0;
            }

            .admin-sidebar .nav-link {
                color: #94a3b8;
                border-radius: 12px;
                padding: 0.55rem 0.85rem;
            }

            .admin-sidebar .nav-link.active {
                color: #e2e8f0;
                background: rgba(14, 165, 233, 0.22);
                font-weight: 600;
            }

            .admin-main {
                flex: 1;
                min-width: 0;
                display: flex;
                flex-direction: column;
            }

            .admin-header {
                position: sticky;
                top: 0;
                z-index: 1020;
                background: rgba(255, 255, 255, 0.86);
                backdrop-filter: blur(18px);
                border-bottom: 1px solid #e2e8f0;
            }

            .admin-content {
                padding: 1.75rem 2rem 2.5rem;
            }

            .admin-breadcrumbs {
                font-size: 0.9rem;
                color: #64748b;
            }

            .admin-table {
                border-radius: 14px;
                overflow: hidden;
                border: 1px solid #e2e8f0;
                background: #ffffff;
            }

            .admin-pill {
                border-radius: 999px;
                padding: 0.2rem 0.7rem;
                font-size: 0.75rem;
                font-weight: 600;
            }

            .admin-toasts {
                position: fixed;
                top: 1.2rem;
                right: 1.2rem;
                z-index: 1085;
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }

            @media (max-width: 991.98px) {
                .admin-shell {
                    flex-direction: column;
                }

                .admin-content {
                    padding: 1.25rem;
                }
            }
        </style>
    @endif
    @stack('head')
</head>

<body class="admin-body">
    <div class="admin-shell">
        <x-admin.sidebar />

        <div class="admin-main">
            <x-admin.header />

            <main class="admin-content">
                <x-admin.breadcrumbs />

                @if ($errors->any())
                    <x-admin.validation-summary />
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <x-admin.toasts />
    @if (!$useVite)
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
                    document.querySelectorAll('.toast').forEach(function(toastEl) {
                        new bootstrap.Toast(toastEl, {
                            delay: 5000
                        }).show();
                    });
                }

                var toggle = document.querySelector('[data-theme-toggle]');
                if (toggle) {
                    toggle.addEventListener('click', function() {
                        var current = document.documentElement.getAttribute('data-bs-theme') || 'light';
                        var next = current === 'dark' ? 'light' : 'dark';
                        document.documentElement.setAttribute('data-bs-theme', next);
                    });
                }
            });
        </script>
    @endif
    @stack('scripts')
    <!-- Confirm Modal (global) -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="text-muted" id="confirmModalText">Are you sure?</div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmModalYes">Confirm</button>
                </div>
            </div>
        </div>
    </div>


</body>

</html>
