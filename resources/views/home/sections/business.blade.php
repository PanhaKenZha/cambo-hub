<x-home-section :section="$section">
    <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($items as $item)
            <li>
                <x-ui.card as="article" padding="p-5" interactive class="flex h-full flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <img src="{{ $item->imageUrl('images/placeholders/company.svg') }}" width="56" height="56" alt="" loading="lazy" class="size-14 shrink-0 rounded-2xl border border-line bg-surface-muted object-cover">
                        <div class="min-w-0">
                            <h3 class="text-base leading-snug font-semibold">@if($item->linkUrl())<a href="{{ $item->linkUrl() }}" class="stretched-link">{{ $item->title }}</a>@else{{ $item->title }}@endif</h3>
                            @if($item->badge)<x-ui.badge class="mt-1">{{ $item->badge }}</x-ui.badge>@endif
                        </div>
                    </div>
                    @if($item->summary)<p class="text-sm text-ink-muted">{{ $item->summary }}</p>@endif
                    <div class="mt-auto flex items-center justify-between gap-3 border-t border-line pt-3 text-xs text-ink-muted">
                        @if($item->detail('location'))<span class="flex min-w-0 items-center gap-1.5"><x-ui.icon name="location" :size="14" /><span class="truncate">{{ $item->detail('location') }}</span></span>@endif
                        @if($item->detail('rating'))<span class="flex shrink-0 items-center gap-1"><img src="{{ asset('images/icons/star-red.svg') }}" width="13" height="13" alt="{{ __('Rating') }}">{{ $item->detail('rating') }}</span>@endif
                    </div>
                </x-ui.card>
            </li>
        @endforeach
    </ul>
</x-home-section>
