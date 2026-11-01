@php
    /**
     * Search box + counted filter tabs, on the same shell as the women
     * directory and the risk-alert queues.
     *
     * Tabs are links (they carry the current search and any $preserve params)
     * and the search is a GET form (it carries the active tab), so the two
     * filters never wipe each other out and no JavaScript is required.
     *
     * @param array  $tabs        key => ['label' => string, 'count' => ?int]
     * @param string $active      current tab key
     * @param string $param       query param holding the tab key, default "status"
     * @param string $action      form action URL
     * @param string $search      current search term
     * @param string $searchName  query param for the term, default "search"
     * @param array  $preserve    extra query params to keep (e.g. barangay, dates)
     * @param string $placeholder
     * @param string $label       accessible name for the tab strip
     */
    $tabs = $tabs ?? [];
    $active = $active ?? '';
    $param = $param ?? 'status';
    $search = (string) ($search ?? '');
    $searchName = $searchName ?? 'search';
    $placeholder = $placeholder ?? 'Search...';
    $label = $label ?? 'Filters';
    $preserve = array_filter($preserve ?? [], fn ($value) => $value !== null && $value !== '');
    $formId = 'listToolbar' . md5($action . $param);

    $tabUrl = function (string $key) use ($action, $param, $search, $searchName, $preserve) {
        $query = array_merge($preserve, [
            $param => $key,
            $searchName => $search !== '' ? $search : null,
        ]);
        $query = array_filter($query, fn ($value) => $value !== null && $value !== '');

        return $query === [] ? $action : $action . '?' . http_build_query($query);
    };
@endphp

<div class="wd-toolbar d-flex flex-wrap align-items-end gap-2 mb-3 fade-in-card">
    @if ($tabs !== [])
        <ul class="wd-tabs mb-0 flex-grow-1" role="tablist" aria-label="{{ $label }}">
            @foreach ($tabs as $key => $meta)
                <li>
                    <a class="wd-tab {{ $active === $key ? 'active' : '' }}"
                       href="{{ $tabUrl((string) $key) }}"
                       role="tab"
                       @if ($active === $key) aria-current="true" @endif>
                        {{ $meta['label'] }}@if (isset($meta['count']))&nbsp;({{ number_format($meta['count']) }})@endif
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    <form method="GET" action="{{ $action }}" id="{{ $formId }}" class="wd-search ms-auto">
        @if ($tabs !== [])
            <input type="hidden" name="{{ $param }}" value="{{ $active }}">
        @endif
        @foreach ($preserve as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
        <input type="search" name="{{ $searchName }}" class="form-control" id="{{ $formId }}Input"
               value="{{ $search }}" placeholder="{{ $placeholder }}" autocomplete="off"
               aria-label="{{ $placeholder }}">
        <button class="btn" type="submit" title="Search"><i class="bi bi-search"></i></button>
    </form>

    @if ($search !== '' || ($active !== '' && count($tabs) > 1))
        <a href="{{ $action }}" class="btn btn-sm btn-link text-decoration-none fw-semibold wd-reset"
           title="Clear search and filters">
            <i class="bi bi-x-circle me-1"></i> Reset
        </a>
    @endif
</div>
