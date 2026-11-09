<x-home-section :section="$section">
    <ul class="grid grid-cols-2 gap-3 min-[480px]:grid-cols-3 sm:gap-4 md:grid-cols-4 lg:grid-cols-6">
        @foreach($items as $item)
            <li>
                <x-ui.card padding="px-3 py-5" interactive class="flex h-full flex-col items-center gap-2.5 text-center">
                    <span class="grid size-12 place-items-center rounded-2xl bg-icon-tile">
                        <img src="{{ $item->imageUrl('images/icons/'.$item->detail('icon', 'menu').'-blue.svg') }}" width="26" height="26" alt="" loading="lazy" class="size-6.5">
                    </span>
                    <h3 class="text-sm font-semibold">@if($item->linkUrl())<a href="{{ $item->linkUrl() }}" class="stretched-link">{{ $item->title }}</a>@else{{ $item->title }}@endif</h3>
                    @if($item->summary)<p class="hidden text-xs leading-snug text-ink-subtle sm:block">{{ $item->summary }}</p>@endif
                </x-ui.card>
            </li>
        @endforeach
    </ul>
</x-home-section>
