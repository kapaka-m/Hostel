<!doctype html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name', 'Hostel') }}</title>

    @php
        $useVite = file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json'));
        $bgPath = public_path('images/login-bg.jpg');
        $bgUrl = file_exists($bgPath) ? asset('images/login-bg.jpg') : null;
    @endphp

    @if ($useVite)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    @endif

    <style>
        :root {
            --login-radius: 18px;
        }

        body.login-body {
            font-family: "Space Grotesk", "Segoe UI", sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .login-bg {
            position: fixed;
            inset: 0;

            background: @if ($bgUrl)
                url('{{ $bgUrl }}')
            @else
                radial-gradient(1200px 600px at 20% 10%, rgba(14, 165, 233, .35), transparent 60%),
                radial-gradient(900px 500px at 90% 20%, rgba(99, 102, 241, .25), transparent 55%),
                linear-gradient(135deg, #0b1120 0%, #0f172a 45%, #111827 100%)
            @endif
            ;
            background-size: cover;
            background-position: center;
            transform: scale(1.05);
            filter: saturate(1.1);
            z-index: 0;
        }

        /* overlay blur layer */
        .login-overlay {
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, 0.45);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            z-index: 1;
        }

        .login-shell {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 2.5rem 1rem;
        }

        .login-card {
            width: 100%;
            max-width: 460px;
            border-radius: var(--login-radius);
            border: 1px solid rgba(226, 232, 240, 0.28);
            background: rgba(255, 255, 255, 0.75);
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.25);
            overflow: hidden;
        }

        [data-bs-theme="dark"] .login-card {
            background: rgba(17, 24, 39, 0.72);
            border-color: rgba(148, 163, 184, 0.18);
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.45);
        }

        .login-card-header {
            padding: 1.25rem 1.5rem 0.75rem;
        }

        .login-brand {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .login-badge {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-weight: 800;
            color: #fff;
            background: linear-gradient(135deg, #0ea5e9, #6366f1);
            box-shadow: 0 14px 30px rgba(14, 165, 233, .25);
        }

        .login-subtitle {
            color: rgba(15, 23, 42, 0.72);
        }

        [data-bs-theme="dark"] .login-subtitle {
            color: rgba(226, 232, 240, 0.72);
        }

        .login-card-body {
            padding: 1rem 1.5rem 1.5rem;
        }

        .form-control {
            border-radius: 12px;
        }

        .btn-login {
            border-radius: 12px;
            font-weight: 600;
            padding: .75rem 1rem;
        }

        .password-wrap {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: .75rem;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #64748b;
            font-weight: 600;
        }

        .footer-hint {
            margin-top: .75rem;
            font-size: .9rem;
            color: rgba(15, 23, 42, 0.6);
        }

        [data-bs-theme="dark"] .footer-hint {
            color: rgba(226, 232, 240, 0.6);
        }
    </style>
</head>

<body class="login-body">
    <div class="login-bg"></div>
    <div class="login-overlay"></div>

    <div class="login-shell">
        <div class="login-card">
            <div class="login-card-header">
                <div class="login-brand">
                    <div class="login-badge">H</div>
                    <div>
                        <div class="h5 mb-0">Hostel Admin</div>
                        <div class="login-subtitle small">Sign in to manage dorms, students, rooms.</div>
                    </div>
                </div>
            </div>

            <div class="login-card-body">
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <div class="fw-semibold mb-1"></div>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ url('/login') }}" id="login-form">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                            autocomplete="email" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="password-wrap">
                            <input type="password" name="password" class="form-control" required
                                autocomplete="current-password" id="password-input">
                            <button type="button" class="password-toggle" id="password-toggle"
                                aria-label="Toggle password">
                                Show
                            </button>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100 btn-login" type="submit" id="login-submit">
                        <span class="me-2" id="login-spinner" style="display:none;">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </span>
                        Sign In
                    </button>

                    <div class="footer-hint text-center">
                        Protected area • IP restricted • Role-based access
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if (!$useVite)
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>
    @endif

    <script>
        (function() {
            const passInput = document.getElementById('password-input');
            const toggle = document.getElementById('password-toggle');
            const form = document.getElementById('login-form');
            const btn = document.getElementById('login-submit');
            const spinner = document.getElementById('login-spinner');

            if (toggle && passInput) {
                toggle.addEventListener('click', () => {
                    const isPassword = passInput.getAttribute('type') === 'password';
                    passInput.setAttribute('type', isPassword ? 'text' : 'password');
                    toggle.textContent = isPassword ? 'Hide' : 'Show';
                });
            }

            if (form && btn && spinner) {
                form.addEventListener('submit', () => {
                    btn.disabled = true;
                    spinner.style.display = 'inline-block';
                });
            }
        })();
    </script>
</body>

</html>
