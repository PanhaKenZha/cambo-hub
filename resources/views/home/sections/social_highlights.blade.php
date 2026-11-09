<x-home-section :section="$section">
    <ul class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @foreach($items as $item)
            <li>
                <x-ui.card as="article" padding="p-5" interactive class="flex h-full flex-col gap-3.5">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset($item->detail('avatar', 'images/placeholders/avatar.svg')) }}" width="40" height="40" alt="" loading="lazy" class="size-10 rounded-full bg-surface-muted object-cover">
                        <div class="min-w-0">
                            <strong class="block truncate text-sm">{{ $item->detail('author', __('Cambo Hub member')) }}</strong>
                            @if($item->published_at)<time class="text-xs text-ink-subtle" datetime="{{ $item->published_at->toIso8601String() }}">{{ $item->published_at->diffForHumans() }}</time>@endif
                        </div>
                    </div>
                    <h3 class="sr-only">{{ $item->title }}</h3>
                    @if($item->summary)<p class="text-sm leading-relaxed text-ink">{{ $item->summary }}</p>@endif
                    @if($item->imageUrl())
                        <div class="skeleton overflow-hidden rounded-xl"><img src="{{ $item->imageUrl() }}" width="360" height="210" alt="" loading="lazy" data-placeholder class="aspect-[16/9] w-full object-cover"></div>
                    @endif
                    <div class="mt-auto flex items-center gap-5 border-t border-line pt-3 text-xs text-ink-muted">
                        <span class="flex items-center gap-1.5"><img src="{{ asset('images/icons/heart-red.svg') }}" width="14" height="14" alt="">{{ trans_choice(':count like|:count likes', (int) $item->detail('likes', 0)) }}</span>
                        <span class="flex items-center gap-1.5"><x-ui.icon name="messages" :size="14" />{{ trans_choice(':count comment|:count comments', (int) $item->detail('comments', 0)) }}</span>
                        @if($item->linkUrl())<a href="{{ $item->linkUrl() }}" class="stretched-link"><span class="sr-only">{{ $item->title }}</span></a>@endif
                    </div>
                </x-ui.card>
            </li>
        @endforeach
    </ul>
</x-home-section>
