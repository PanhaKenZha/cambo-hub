<x-home-section :section="$section">
    <ul class="service-directory grid grid-cols-1 gap-3 min-[480px]:grid-cols-2 sm:gap-4 lg:grid-cols-3">
        @foreach($items as $item)
            <li>
                <x-ui.card padding="p-5" interactive class="group grid h-full grid-cols-[3.5rem_minmax(0,1fr)] items-center gap-x-4 gap-y-1 overflow-hidden">
                    <span class="row-span-2 grid size-14 place-items-center rounded-2xl bg-icon-tile transition duration-300 group-hover:scale-110 group-hover:-rotate-6 group-hover:shadow-card">
                        <img src="{{ $item->imageUrl('images/icons/'.$item->detail('icon', 'menu').'-blue.svg') }}" width="26" height="26" alt="" loading="lazy" class="size-6.5">
                    </span>
                    <h3 class="text-sm font-semibold">@if($item->linkUrl())<a href="{{ $item->linkUrl() }}" class="stretched-link">{{ $item->title }}</a>@else{{ $item->title }}@endif</h3>
                    @if($item->summary)<p class="line-clamp-2 text-xs leading-relaxed text-ink-muted">{{ $item->summary }}</p>@endif
                </x-ui.card>
            </li>
        @endforeach
    </ul>
</x-home-section>
