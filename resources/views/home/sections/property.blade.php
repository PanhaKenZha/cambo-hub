<x-home-section :section="$section">
    <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($items as $item)
            <li>
                <x-ui.card as="article" padding="p-0" interactive class="flex h-full flex-col overflow-hidden">
                    <div class="skeleton relative aspect-[3/2]">
                        <img src="{{ $item->imageUrl('images/backgrounds/real-estate.webp') }}" width="320" height="214" alt="" loading="lazy" data-placeholder class="size-full object-cover">
                        @if($item->badge)<x-ui.badge class="absolute top-3 left-3 bg-surface! shadow-sm">{{ $item->badge }}</x-ui.badge>@endif
                    </div>
                    <div class="flex flex-1 flex-col gap-1.5 p-4">
                        @if($item->detail('price'))<strong class="text-lg text-primary">{{ $item->detail('price') }}</strong>@endif
                        <h3 class="line-clamp-2 text-sm leading-snug font-semibold">@if($item->linkUrl())<a href="{{ $item->linkUrl() }}" class="stretched-link">{{ $item->title }}</a>@else{{ $item->title }}@endif</h3>
                        @if($item->detail('location'))<p class="flex items-center gap-1.5 text-xs text-ink-muted"><x-ui.icon name="location" :size="14" />{{ $item->detail('location') }}</p>@endif
                        <ul class="mt-auto flex flex-wrap gap-1.5 border-t border-line pt-3">
                            @if($item->detail('bedrooms'))<li class="rounded-md bg-surface-muted px-2 py-0.5 text-[11px] text-ink-muted">{{ trans_choice(':count bed|:count beds', $item->detail('bedrooms')) }}</li>@endif
                            @if($item->detail('bathrooms'))<li class="rounded-md bg-surface-muted px-2 py-0.5 text-[11px] text-ink-muted">{{ trans_choice(':count bath|:count baths', $item->detail('bathrooms')) }}</li>@endif
                            @if($item->detail('area'))<li class="rounded-md bg-surface-muted px-2 py-0.5 text-[11px] text-ink-muted">{{ $item->detail('area') }}</li>@endif
                        </ul>
                    </div>
                </x-ui.card>
            </li>
        @endforeach
    </ul>
</x-home-section>
