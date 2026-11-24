{{-- Pagination d'une archive : précédent, numéros, suivant. --}}
@if ($paginator->hasPages())
    <nav class="gp-pagination" aria-label="{{ __('Navigation entre les pages', [], $locale) }}">
        @if ($paginator->onFirstPage())
            <span class="gp-pagination__item is-disabled" aria-hidden="true">←</span>
        @else
            <a class="gp-pagination__item" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('Page précédente', [], $locale) }}">←</a>
        @endif
        @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $number => $url)
            @if ($number === $paginator->currentPage())
                <span class="gp-pagination__item is-current" aria-current="page">{{ $number }}</span>
            @else
                <a class="gp-pagination__item" href="{{ $url }}">{{ $number }}</a>
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <a class="gp-pagination__item" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('Page suivante', [], $locale) }}">→</a>
        @else
            <span class="gp-pagination__item is-disabled" aria-hidden="true">→</span>
        @endif
    </nav>
@endif
