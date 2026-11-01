{{-- Results count on the left, prev/next chevrons on the right, no number strip.
     Uses Bootstrap's .pagination/.page-link markup so theme.css:87-90 paints the
     same pills as the rest of the app, and stays visible below the sm breakpoint
     where the framework view swaps to worded Previous/Next buttons. --}}
@if ($paginator->hasPages())
    <nav class="d-flex align-items-center justify-content-between gap-2" aria-label="Pagination">
        <p class="small text-muted mb-0 d-none d-sm-block">
            {!! __('Showing') !!}
            <span class="fw-semibold">{{ $paginator->firstItem() }}</span>
            {!! __('to') !!}
            <span class="fw-semibold">{{ $paginator->lastItem() }}</span>
            {!! __('of') !!}
            <span class="fw-semibold">{{ $paginator->total() }}</span>
            {!! __('results') !!}
        </p>

        <ul class="pagination mb-0 ms-auto">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" aria-label="Previous page">&lsaquo;</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page">&lsaquo;</a>
                </li>
            @endif

            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page">&rsaquo;</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" aria-label="Next page">&rsaquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
