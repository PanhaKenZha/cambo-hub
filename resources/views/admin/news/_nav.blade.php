{{-- Sub-navigation shared by every News admin page. --}}
@php
    $newsTabs = [
        ['label' => 'Articles', 'route' => 'admin.news.articles.index', 'active' => 'admin.news.articles.*'],
        ['label' => 'Categories', 'route' => 'admin.news.categories.index', 'active' => 'admin.news.categories.*'],
        ['label' => 'Authors', 'route' => 'admin.news.authors.index', 'active' => 'admin.news.authors.*'],
        ['label' => 'Comments', 'route' => 'admin.news.comments.index', 'active' => 'admin.news.comments.*',
            'count' => \App\Models\NewsComment::where('status', \App\Enums\CommentStatus::Pending)->count()],
    ];
@endphp
<div class="mb-6">
    <div class="flex items-center gap-3">
        <span class="flex size-10 items-center justify-center rounded-xl bg-primary-soft text-primary ring-1 ring-primary/15">
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z"/></svg>
        </span>
        <div>
            <p class="text-xs font-semibold tracking-[0.15em] text-ink-subtle uppercase">Module</p>
            <p class="text-lg leading-tight font-bold text-ink">News</p>
        </div>
        @unless(\App\Enums\PlatformModule::News->isEnabled())
            <x-admin.badge color="amber" class="ml-2">Module is turned off — readers can't see news</x-admin.badge>
        @endunless
    </div>
    <nav class="mt-4 flex gap-1 overflow-x-auto border-b border-line" aria-label="News sections">
        @foreach($newsTabs as $tab)
            @php($isActive = request()->routeIs($tab['active']))
            <a href="{{ route($tab['route']) }}" @if($isActive) aria-current="page" @endif
               @class([
                   '-mb-px flex shrink-0 items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap',
                   'border-primary text-primary' => $isActive,
                   'border-transparent text-ink-muted hover:border-line-strong hover:text-ink' => ! $isActive,
               ])>
                {{ $tab['label'] }}
                @if(($tab['count'] ?? 0) > 0)
                    <span class="rounded-full bg-danger px-2 py-0.5 text-[11px] leading-none font-semibold text-white tabular-nums">{{ $tab['count'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>
</div>
