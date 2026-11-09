<x-home-section :section="$section">
    <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($items as $item)
            @php($eventDate = $item->detail('date') ? \Illuminate\Support\Carbon::parse($item->detail('date')) : null)
            <li>
                <x-ui.card as="article" padding="p-0" interactive class="flex h-full flex-col overflow-hidden">
                    <div class="skeleton relative aspect-[16/10]">
                        <img src="{{ $item->imageUrl('images/backgrounds/events.webp') }}" width="320" height="200" alt="" loading="lazy" data-placeholder class="size-full object-cover">
                        @if($eventDate)
                            <time datetime="{{ $eventDate->toIso8601String() }}" class="absolute top-3 left-3 flex flex-col items-center rounded-xl bg-surface-raised px-3 py-1.5 leading-none shadow-card">
                                <strong class="text-lg text-primary">{{ $eventDate->format('j') }}</strong>
                                <span class="mt-0.5 text-[10px] font-semibold text-accent uppercase">{{ $eventDate->translatedFormat('M') }}</span>
                            </time>
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col gap-2 p-4">
                        @if($item->badge)<x-ui.badge class="w-fit">{{ $item->badge }}</x-ui.badge>@endif
                        <h3 class="line-clamp-2 text-sm leading-snug font-semibold">@if($item->linkUrl())<a href="{{ $item->linkUrl() }}" class="stretched-link">{{ $item->title }}</a>@else{{ $item->title }}@endif</h3>
                        <ul class="mt-auto space-y-1 text-xs text-ink-muted">
                            @if($eventDate)<li class="flex items-center gap-1.5"><x-ui.icon name="events" :size="14" />{{ $eventDate->translatedFormat('D, j M · H:i') }}</li>@endif
                            @if($item->detail('venue'))<li class="flex items-center gap-1.5"><x-ui.icon name="location" :size="14" /><span class="truncate">{{ $item->detail('venue') }}</span></li>@endif
                        </ul>
                    </div>
                </x-ui.card>
            </li>
        @endforeach
    </ul>
</x-home-section>
