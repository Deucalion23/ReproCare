@extends('user.layout')

@section('title', 'Log Period - ReproCare')

@section('user-content')
<div class="py-2">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Log Period</h1>
            <p class="page-subtitle">Record your menstrual period dates</p>
        </div>
        <a href="{{ route('user.menstruation.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body p-4">

                    <!-- Error display -->
                    @if($errors->any())
                        <div class="alert alert-danger mb-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('user.menstruation.store') }}">
                        @csrf

                        <!-- Banner -->
                        <div style="background:color-mix(in srgb, var(--color-danger) 8%, transparent); border:1px solid color-mix(in srgb, var(--color-danger) 20%, transparent); border-radius:12px; padding:1rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.75rem;">
                            <div style="width:40px; height:40px; border-radius:50%; background:color-mix(in srgb, var(--color-danger) 15%, transparent); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <i class="bi bi-droplet-fill" style="color:var(--color-danger-text); font-size:1.1rem;"></i>
                            </div>
                            <div>
                                <div style="font-size:0.875rem; font-weight:600; color:var(--text);">Add Period Record</div>
                                <div style="font-size:0.78rem; color:var(--text-muted);">Enter the first and last day of your period</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="start_date" class="form-label">
                                <i class="bi bi-calendar me-1" style="color:var(--primary-light);"></i>
                                First Day of Period
                            </label>
                            <input type="date" class="form-control" id="start_date" name="start_date"
                                   value="{{ old('start_date') }}" required>
                            <div class="form-text">The day your period bleeding started</div>
                        </div>

                        <div class="mb-4">
                            <label for="end_date" class="form-label">
                                <i class="bi bi-calendar-check me-1" style="color:var(--primary-light);"></i>
                                Last Day of Period
                            </label>
                            <input type="date" class="form-control" id="end_date" name="end_date"
                                   value="{{ old('end_date') }}" required>
                            <div class="form-text">The day your period ended</div>
                        </div>

                        <!-- Duration preview -->
                        <div id="durationPreview" class="d-none mb-4">
                            <div style="background:var(--bg-card2); border:none; border-radius:10px; padding:0.75rem 1rem; display:flex; align-items:center; gap:0.5rem;">
                                <i class="bi bi-clock-history" style="color:var(--primary-light);"></i>
                                <span style="font-size:0.875rem; color:var(--text-muted);">Duration: </span>
                                <strong id="durationText" style="color:var(--text);"></strong>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-1"></i> Save Period Record
                            </button>
                            <a href="{{ route('user.menstruation.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const startInput = document.getElementById('start_date');
    const endInput   = document.getElementById('end_date');
    const preview    = document.getElementById('durationPreview');
    const durationTxt = document.getElementById('durationText');

    function updateDuration() {
        if (startInput.value && endInput.value) {
            const start = new Date(startInput.value);
            const end   = new Date(endInput.value);
            const diff  = Math.round((end - start) / (1000 * 60 * 60 * 24)) + 1;
            if (diff > 0) {
                durationTxt.textContent = diff + ' day' + (diff !== 1 ? 's' : '');
                preview.classList.remove('d-none');
            } else {
                preview.classList.add('d-none');
            }
        }
    }

    startInput.addEventListener('change', updateDuration);
    endInput.addEventListener('change', updateDuration);
</script>
@endpush
@endsection
