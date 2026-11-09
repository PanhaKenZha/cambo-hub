{{-- Status tabs for moderation queues. Expects: $tabs (value => label), $activeStatus, $statusCounts, $routeName --}}
<nav class="flex gap-1 overflow-x-auto border-b border-slate-200" aria-label="Filter by status">
    @foreach($tabs as $value => $label)
        @php($isActive = $value === $activeStatus)
        <a href="{{ route($routeName, array_merge(request()->except(['status', 'page']), ['status' => $value])) }}" @if($isActive) aria-current="page" @endif
           @class([
               '-mb-px flex shrink-0 items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap transition',
               'border-brand-600 text-brand-700' => $isActive,
               'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' => ! $isActive,
           ])>
            {{ $label }}
            @if($value !== 'all')
                <span @class(['rounded-full px-2 py-0.5 text-xs tabular-nums', 'bg-brand-100 text-brand-700' => $isActive, 'bg-slate-100 text-slate-600' => ! $isActive])>{{ $statusCounts[$value] ?? 0 }}</span>
            @endif
        </a>
    @endforeach
</nav>
