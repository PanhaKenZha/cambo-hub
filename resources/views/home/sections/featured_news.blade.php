<x-home-section :section="$section">
    @php($lead = $items->first())
    <div class="grid gap-5 lg:grid-cols-[1.3fr_1fr]">
        <x-ui.card as="article" padding="p-0" interactive class="overflow-hidden">
            @if($lead->imageUrl())
                <div class="skeleton aspect-video"><img src="{{ $lead->imageUrl() }}" width="640" height="360" alt="" loading="lazy" data-placeholder class="size-full object-cover"></div>
            @endif
            <div class="p-5 sm:p-6">
                <div class="flex items-center gap-3 text-xs text-ink-subtle">
                    @if($lead->badge)<x-ui.badge>{{ $lead->badge }}</x-ui.badge>@endif
                    @if($lead->published_at)<time datetime="{{ $lead->published_at->toIso8601String() }}">{{ $lead->published_at->translatedFormat('j M Y') }}</time>@endif
                </div>
                <h3 class="mt-3 text-lg leading-snug font-bold sm:text-xl">@if($lead->linkUrl())<a href="{{ $lead->linkUrl() }}" class="stretched-link">{{ $lead->title }}</a>@else{{ $lead->title }}@endif</h3>
                @if($lead->summary)<p class="mt-2 text-sm text-ink-muted">{{ $lead->summary }}</p>@endif
            </div>
        </x-ui.card>
        <ul class="grid content-start gap-4">
            @foreach($items->skip(1) as $item)
                <li>
                    <x-ui.card as="article" padding="p-3" interactive class="flex items-center gap-4">
                        <div class="skeleton size-20 shrink-0 overflow-hidden rounded-xl sm:h-20 sm:w-28">
                            <img src="{{ $item->imageUrl('images/backgrounds/news.webp') }}" width="112" height="80" alt="" loading="lazy" data-placeholder class="size-full object-cover">
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 text-[11px] text-ink-subtle">
                                @if($item->badge)<x-ui.badge>{{ $item->badge }}</x-ui.badge>@endif
                                @if($item->published_at)<time datetime="{{ $item->published_at->toIso8601String() }}">{{ $item->published_at->translatedFormat('j M Y') }}</time>@endif
                            </div>
                            <h3 class="mt-1.5 line-clamp-2 text-sm leading-snug font-semibold">@if($item->linkUrl())<a href="{{ $item->linkUrl() }}" class="stretched-link">{{ $item->title }}</a>@else{{ $item->title }}@endif</h3>
                        </div>
                    </x-ui.card>
                </li>
            @endforeach
        </ul>
    </div>
</x-home-section>
