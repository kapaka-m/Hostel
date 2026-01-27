@php
    $messages = [];

    if (session('success')) {
        $messages[] = ['type' => 'success', 'title' => 'Success', 'body' => session('success')];
    }

    if (session('error')) {
        $messages[] = ['type' => 'danger', 'title' => 'Error', 'body' => session('error')];
    }

    if (session('generated_password')) {
        $messages[] = ['type' => 'warning', 'title' => 'Generated Password', 'body' => session('generated_password')];
    }

    if (session('status')) {
        $messages[] = ['type' => 'info', 'title' => 'Notice', 'body' => session('status')];
    }
@endphp

@if ($messages)
    <div class="admin-toasts">
        @foreach ($messages as $message)
            <div class="toast text-bg-{{ $message['type'] }} border-0" role="alert" aria-live="assertive"
                aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong class="me-2">{{ $message['title'] }}</strong>
                        {{ $message['body'] }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        @endforeach
    </div>
@endif
