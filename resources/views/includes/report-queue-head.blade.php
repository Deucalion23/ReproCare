@php
    /**
     * Slim header for the BHW monthly-report queues: one bar carries what the
     * queue is, the flash message, and the whole-queue counts as chips.
     * Replaces the tall page-hero plus the four-card metric grid, which together
     * pushed the first report row ~300px down the screen.
     *
     * @param string $title
     * @param string $subtitle
     * @param string $icon   bootstrap-icons class
     * @param array  $chips  list of ['label' => string, 'value' => int, 'tone' => primary|warning|success|danger|info]
     */
    $chips = $chips ?? [];
    $icon = $icon ?? 'bi-clipboard2-check-fill';
@endphp

<div class="rq-bar fade-in-card">
    <div class="rq-bar-id">
        <div class="rq-bar-title"><i class="bi {{ $icon }}" aria-hidden="true"></i>{{ $title }}</div>
        @if (!empty($subtitle))
            <p class="rq-bar-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @if ($chips !== [])
        <div class="rq-chips" aria-label="Queue counts">
            @foreach ($chips as $chip)
                <span class="summary-chip chip-{{ $chip['tone'] ?? 'primary' }} rq-chip">
                    <b>{{ number_format((int) $chip['value']) }}</b>{{ $chip['label'] }}
                </span>
            @endforeach
        </div>
    @endif
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-0">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-0">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
