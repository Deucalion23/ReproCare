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
     * @param string $action_href  optional primary button, e.g. the Create Report link
     * @param string $action_label
     * @param string $action_icon
     * @param array  $actions  list of ['href' => string, 'label' => string, 'icon' => string,
     *                          'target' => string] for pages whose authority is more than one
     *                          button (e.g. the RHU and CHO CSV + PDF exports)
     * @param string $bar_class  extra classes for pages that are not inside a .workspace-stack
     *                          and so get no gap from their parent
     */
    $chips = $chips ?? [];
    $icon = $icon ?? 'bi-clipboard2-check-fill';
    $barClass = $bar_class ?? '';
    $actions = $actions ?? [];
    if (!empty($action_href)) {
        $actions[] = [
            'href' => $action_href,
            'label' => $action_label ?? 'Create Report',
            'icon' => $action_icon ?? 'bi-plus-circle-fill',
        ];
    }
@endphp

<div class="rq-bar fade-in-card {{ $barClass }}">
    <div class="rq-bar-id">
        <div class="rq-bar-title"><i class="bi {{ $icon }}" aria-hidden="true"></i>{{ $title }}</div>
        @if (!empty($subtitle))
            <p class="rq-bar-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @if ($chips !== [])
        <div class="rq-chips" aria-label="Queue counts">
            @foreach ($chips as $chip)
                {{-- Rates arrive as floats (facility delivery %), counts as ints: keep one
                     decimal only when the value actually has one. --}}
                <span class="summary-chip chip-{{ $chip['tone'] ?? 'primary' }} rq-chip">
                    <b>{{ number_format($chip['value'], floor($chip['value']) == $chip['value'] ? 0 : 1) }}</b>{{ $chip['label'] }}
                </span>
            @endforeach
        </div>
    @endif

    @foreach ($actions as $action)
        <a href="{{ $action['href'] }}" class="rq-action"
           @if (!empty($action['target'])) target="{{ $action['target'] }}" rel="noopener" @endif>
            <i class="bi {{ $action['icon'] ?? 'bi-plus-circle-fill' }}" aria-hidden="true"></i>{{ $action['label'] }}
        </a>
    @endforeach
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
