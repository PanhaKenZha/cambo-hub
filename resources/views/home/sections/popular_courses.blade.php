<x-home-section :section="$section">
    <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($items as $item)
            <li>
                <x-ui.card as="article" padding="p-0" interactive class="flex h-full flex-col overflow-hidden">
                    <div class="skeleton relative aspect-[16/10]">
                        <img src="{{ $item->imageUrl('images/backgrounds/education.webp') }}" width="320" height="200" alt="" loading="lazy" data-placeholder class="size-full object-cover">
                        @if($item->badge)<x-ui.badge class="absolute top-3 left-3 bg-surface! shadow-sm">{{ $item->badge }}</x-ui.badge>@endif
                    </div>
                    <div class="flex flex-1 flex-col gap-1.5 p-4">
                        <h3 class="line-clamp-2 text-sm leading-snug font-semibold">@if($item->linkUrl())<a href="{{ $item->linkUrl() }}" class="stretched-link">{{ $item->title }}</a>@else{{ $item->title }}@endif</h3>
                        @if($item->detail('provider'))<p class="truncate text-xs text-ink-subtle">{{ $item->detail('provider') }}</p>@endif
                        <div class="mt-auto flex items-center justify-between gap-2 border-t border-line pt-3 text-xs text-ink-muted">
                            @if($item->detail('duration'))<span class="flex items-center gap-1.5"><x-ui.icon name="education" :size="14" />{{ $item->detail('duration') }}</span>@endif
                            @if($item->detail('rating'))<span class="flex items-center gap-1"><img src="{{ asset('images/icons/star-red.svg') }}" width="13" height="13" alt="{{ __('Rating') }}">{{ $item->detail('rating') }}</span>@endif
                            @if($item->detail('price'))<strong class="text-sm text-primary">{{ $item->detail('price') }}</strong>@endif
                        </div>
                    </div>
                </x-ui.card>
            </li>
        @endforeach
    </ul>
</x-home-section>
