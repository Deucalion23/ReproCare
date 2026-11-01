{{-- Prev/next only: the women-directory tables are browsed on phones, where a long
     number strip is unusable. Keeps Bootstrap's .pagination markup so theme.css:87-90
     styles the arrows with the same pills as every other list. --}}
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
            <span class="text-muted d-none d-md-inline">· page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
        </p>

        <ul class="pagination mb-0 ms-auto">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" aria-label="Previous page"><i class="bi bi-chevron-left" aria-hidden="true"></i></span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page">
                        <i class="bi bi-chevron-left" aria-hidden="true"></i>
                    </a>
                </li>
            @endif

            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page">
                        <i class="bi bi-chevron-right" aria-hidden="true"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link" aria-label="Next page"><i class="bi bi-chevron-right" aria-hidden="true"></i></span>
                </li>
            @endif
        </ul>
    </nav>
@endif
