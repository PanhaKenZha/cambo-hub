{{-- Page links for a LengthAwarePaginator, styled with the public design tokens. --}}
@props(['paginator'])
@if($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        // First, last and two pages either side of the current one; gaps become "…".
        $pages = collect([1, $last, ...range(max(1, $current - 2), min($last, $current + 2))])->unique()->sort()->values();
        $linkClass = 'grid h-10 min-w-10 place-items-center rounded-xl border px-3 text-sm font-semibold transition';
    @endphp
    <nav {{ $attributes->class(['flex flex-col items-center gap-4 sm:flex-row sm:justify-between']) }} aria-label="{{ __('Pagination') }}">
        <p class="text-sm text-ink-muted">{{ __('Showing :from–:to of :total', ['from' => $paginator->firstItem(), 'to' => $paginator->lastItem(), 'total' => $paginator->total()]) }}</p>
        <ul class="flex flex-wrap items-center gap-1.5">
            <li>
                @if($paginator->onFirstPage())
                    <span class="{{ $linkClass }} cursor-not-allowed border-line text-ink-subtle opacity-60" aria-disabled="true"><span aria-hidden="true">←</span><span class="sr-only">{{ __('Previous') }}</span></span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $linkClass }} border-line bg-surface text-ink hover:border-primary hover:text-primary"><span aria-hidden="true">←</span><span class="sr-only">{{ __('Previous') }}</span></a>
                @endif
            </li>
            @foreach($pages as $index => $page)
                @if($index > 0 && $page - $pages[$index - 1] > 1)
                    <li class="px-1 text-ink-subtle" aria-hidden="true">…</li>
                @endif
                <li>
                    @if($page === $current)
                        <span aria-current="page" class="{{ $linkClass }} border-primary bg-primary text-on-primary">{{ $page }}</span>
                    @else
                        <a href="{{ $paginator->url($page) }}" class="{{ $linkClass }} border-line bg-surface text-ink hover:border-primary hover:text-primary">{{ $page }}</a>
                    @endif
                </li>
            @endforeach
            <li>
                @if($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $linkClass }} border-line bg-surface text-ink hover:border-primary hover:text-primary"><span aria-hidden="true">→</span><span class="sr-only">{{ __('Next') }}</span></a>
                @else
                    <span class="{{ $linkClass }} cursor-not-allowed border-line text-ink-subtle opacity-60" aria-disabled="true"><span aria-hidden="true">→</span><span class="sr-only">{{ __('Next') }}</span></span>
                @endif
            </li>
        </ul>
    </nav>
@endif
