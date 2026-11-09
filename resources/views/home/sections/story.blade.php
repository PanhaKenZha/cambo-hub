<x-home-section :section="$section" compact>
    <ul class="scrollbar-none -mx-4 flex snap-x gap-5 overflow-x-auto px-4 pb-2 sm:mx-0 sm:px-0" tabindex="0" aria-label="{{ __($section->heading) }}">
        @foreach($items as $item)
            <li class="relative flex w-20 shrink-0 snap-start flex-col items-center gap-1.5 text-center">
                <span class="rounded-full bg-[conic-gradient(var(--color-brand-700),var(--color-brand-500),var(--color-cambo-red),var(--color-brand-700))] p-[3px]">
                    <img src="{{ $item->imageUrl('images/placeholders/avatar.svg') }}" width="72" height="72" alt="" loading="lazy" class="size-[4.5rem] rounded-full border-[3px] border-surface bg-surface-muted object-cover">
                </span>
                @if($item->linkUrl())
                    <a href="{{ $item->linkUrl() }}" class="stretched-link line-clamp-2 text-xs font-semibold">{{ $item->title }}</a>
                @else
                    <span class="line-clamp-2 text-xs font-semibold">{{ $item->title }}</span>
                @endif
                @if($item->badge)<small class="text-[10px] text-ink-subtle">{{ $item->badge }}</small>@endif
            </li>
        @endforeach
    </ul>
</x-home-section>
