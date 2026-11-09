{{-- Header menu from Website › Menus. Items with children open a dropdown on desktop and expand inline on mobile. --}}
@props(['items' => [], 'isCurrent', 'href', 'layout' => 'desktop'])
@foreach($items as $item)
    @php($label = __($item['label']))
    @if($item['children'] !== [])
        @php($panelId = 'nav-'.$layout.'-'.$loop->index)
        @php($childIsCurrent = collect($item['children'])->contains(fn (array $child): bool => $isCurrent($child['url'])))
        <div @class(['relative' => $layout === 'desktop', 'sm:col-span-2' => $layout === 'mobile'])>
            <button type="button" data-disclosure aria-controls="{{ $panelId }}" aria-expanded="false" @class([
                'flex w-full cursor-pointer items-center gap-1.5 rounded-xl font-medium transition aria-expanded:bg-surface-muted',
                'px-3 py-2 text-[13px] text-ink-muted hover:bg-surface-muted hover:text-ink' => $layout === 'desktop',
                'justify-between px-4 py-3 text-sm text-ink hover:bg-surface-muted' => $layout === 'mobile',
                'text-primary!' => $childIsCurrent,
            ])>
                {{ $label }}
                <svg class="size-3.5 transition [[aria-expanded=true]>&]:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
            </button>
            <ul id="{{ $panelId }}" hidden @class([
                'absolute left-0 z-50 mt-2 w-60 rounded-2xl border border-line bg-surface-raised p-1.5 shadow-card-hover' => $layout === 'desktop',
                'mt-1 grid gap-1 border-l-2 border-line pl-3 sm:grid-cols-2' => $layout === 'mobile',
            ])>
                {{-- A parent that links somewhere keeps that link as the first entry, since its button only opens the menu. --}}
                @if(filled($item['url']) && $item['url'] !== '#')
                    <li><a href="{{ $href($item['url']) }}" @if($isCurrent($item['url'])) aria-current="page" @endif
                        class="block rounded-xl px-3 py-2.5 text-sm font-semibold text-ink hover:bg-surface-muted aria-[current=page]:bg-primary-soft aria-[current=page]:text-primary">{{ $label }}</a></li>
                @endif
                @foreach($item['children'] as $child)
                    <li><a href="{{ $href($child['url']) }}" @if($child['new_tab']) target="_blank" rel="noopener" @endif @if($isCurrent($child['url'])) aria-current="page" @endif
                        class="block rounded-xl px-3 py-2.5 text-sm text-ink hover:bg-surface-muted aria-[current=page]:bg-primary-soft aria-[current=page]:font-semibold aria-[current=page]:text-primary">{{ __($child['label']) }}</a></li>
                @endforeach
            </ul>
        </div>
    @else
        <a href="{{ $href($item['url']) }}" @if($item['new_tab']) target="_blank" rel="noopener" @endif @if($isCurrent($item['url'])) aria-current="page" @endif
            @class([
                'flex items-center gap-2 rounded-xl font-medium transition',
                'px-3 py-2 text-[13px] text-ink-muted hover:bg-surface-muted hover:text-ink aria-[current=page]:bg-primary-soft aria-[current=page]:text-primary' => $layout === 'desktop',
                'px-4 py-3 text-sm text-ink hover:bg-surface-muted aria-[current=page]:bg-primary-soft aria-[current=page]:text-primary' => $layout === 'mobile',
            ])>{{ $label }}</a>
    @endif
@endforeach
