<x-home-section :section="$section">
    <ul class="grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4">
        @foreach($items as $item)
            <li>
                <x-ui.card as="article" padding="p-0" interactive class="flex h-full flex-col overflow-hidden">
                    <div class="skeleton relative aspect-[4/3]">
                        <img src="{{ $item->imageUrl('images/placeholders/product.svg') }}" width="320" height="240" alt="" loading="lazy" data-placeholder class="size-full object-cover">
                        @if($item->badge)<x-ui.badge variant="solid" class="absolute top-3 left-3">{{ $item->badge }}</x-ui.badge>@endif
                    </div>
                    <div class="flex flex-1 flex-col gap-1 p-3.5 sm:p-4">
                        <h3 class="line-clamp-2 text-sm leading-snug font-semibold">@if($item->linkUrl())<a href="{{ $item->linkUrl() }}" class="stretched-link">{{ $item->title }}</a>@else{{ $item->title }}@endif</h3>
                        @if($item->detail('seller'))<p class="truncate text-xs text-ink-subtle">{{ $item->detail('seller') }}</p>@endif
                        <div class="mt-auto flex items-center justify-between pt-2">
                            @if($item->detail('price'))<strong class="text-base text-primary">{{ $item->detail('price') }}</strong>@endif
                            @if($item->detail('rating'))<span class="flex items-center gap-1 text-xs text-ink-muted"><img src="{{ asset('images/icons/star-red.svg') }}" width="13" height="13" alt="{{ __('Rating') }}">{{ $item->detail('rating') }}</span>@endif
                        </div>
                    </div>
                </x-ui.card>
            </li>
        @endforeach
    </ul>
</x-home-section>
