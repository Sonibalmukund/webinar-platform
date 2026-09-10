@props(['paginator'])

@if(isset($paginator) && method_exists($paginator, 'total') && $paginator->total() > 0)
<div class="admin-pagination-bar mt-3 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 w-100">
    <div class="admin-pagination-info text-muted" style="font-size: 0.84rem; font-weight: 600;">
        Showing <strong class="text-dark">{{ $paginator->firstItem() ?? 0 }}</strong> to <strong class="text-dark">{{ $paginator->lastItem() ?? 0 }}</strong> of <strong class="text-dark">{{ number_format($paginator->total()) }}</strong> entries
    </div>
    @if($paginator->hasPages())
    <div class="admin-pagination-links ms-auto">
        <ul class="pagination mb-0">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link"><i class="bi bi-chevron-left"></i></span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="bi bi-chevron-left"></i></a>
                </li>
            @endif

            @foreach ($paginator->links()->elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"><i class="bi bi-chevron-right"></i></a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link"><i class="bi bi-chevron-right"></i></span>
                </li>
            @endif
        </ul>
    </div>
    @endif
</div>
@endif
