{{-- Status tabs for moderation queues. Expects: $tabs (value => label), $activeStatus, $statusCounts, $routeName --}}
<nav class="flex gap-1 overflow-x-auto border-b border-line" aria-label="Filter by status">
    @foreach($tabs as $value => $label)
        @php($isActive = $value === $activeStatus)
        <a href="{{ route($routeName, array_merge(request()->except(['status', 'page']), ['status' => $value])) }}" @if($isActive) aria-current="page" @endif
           @class([
               '-mb-px flex shrink-0 items-center gap-2 rounded-t-lg border-b-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap transition focus-visible:-outline-offset-3',
               'border-primary text-primary' => $isActive,
               'border-transparent text-ink-muted hover:border-line-strong hover:text-ink' => ! $isActive,
           ])>
            {{ $label }}
            @if($value !== 'all')
                <span @class(['rounded-full px-2 py-0.5 text-xs tabular-nums', 'bg-primary-soft text-primary' => $isActive, 'bg-surface-muted text-ink-muted' => ! $isActive])>{{ $statusCounts[$value] ?? 0 }}</span>
            @endif
        </a>
    @endforeach
</nav>
