<section class="grid gap-4 border-t border-line py-10 lg:grid-cols-2" aria-label="{{ __($section['title']) }}">
    @foreach($items as $item)
        <aside class="relative flex flex-col gap-4 rounded-2xl border border-dashed border-amber-300/70 bg-gradient-to-r from-amber-50 to-surface p-5 sm:flex-row sm:items-center dark:border-amber-500/30 dark:from-amber-500/10">
            <span class="absolute top-2.5 right-4 text-[10px] font-semibold tracking-widest text-amber-700 uppercase dark:text-amber-300">{{ __('Sponsored') }}@if($item->detail('advertiser')) · {{ $item->detail('advertiser') }}@endif</span>
            @if($item->imageUrl())<img src="{{ $item->imageUrl() }}" width="84" height="84" alt="" loading="lazy" class="size-20 shrink-0 rounded-xl object-cover">@endif
            <div class="min-w-0 flex-1">
                <h3 class="text-base font-semibold">{{ $item->title }}</h3>
                @if($item->summary)<p class="mt-0.5 text-sm text-ink-muted">{{ $item->summary }}</p>@endif
            </div>
            @if($item->linkUrl())
                <x-ui.button :href="$item->linkUrl()" size="sm" variant="secondary" class="stretched-link shrink-0" rel="sponsored noopener">{{ $item->detail('cta_label', __('Learn more')) }}</x-ui.button>
            @endif
        </aside>
    @endforeach
</section>
