@if ($errors->any())
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1">Please fix the highlighted fields.</div>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
