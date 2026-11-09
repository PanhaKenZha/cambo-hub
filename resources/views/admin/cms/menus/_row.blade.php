<li @class(['flex items-center gap-4 px-6 py-3', 'bg-slate-50/60 pl-14' => $isChild, 'opacity-60' => ! $item->is_visible])>
    @if($isChild)<span class="text-slate-300" aria-hidden="true">↳</span>@endif
    <div class="min-w-0 flex-1">
        <p class="flex flex-wrap items-center gap-2 text-sm font-medium text-slate-900">
            {{ $item->translate('label', 'en') }}
            @if(filled($item->label['km'] ?? null))<span class="text-slate-500">· {{ $item->label['km'] }}</span>@endif
            @unless($item->is_visible)<x-admin.badge color="gray">Hidden</x-admin.badge>@endunless
            @if($item->opens_in_new_tab)<x-admin.badge color="gray">New tab</x-admin.badge>@endif
        </p>
        <p class="truncate font-mono text-xs text-slate-500">
            @if($item->page)Page: {{ $item->page->translate('title', 'en') }} ({{ $item->page->stateLabel() }})@else{{ $item->url }}@endif
        </p>
    </div>
    @if($canEdit)
        <div class="flex shrink-0 items-center gap-1">
            @foreach(['up' => '&uarr;', 'down' => '&darr;'] as $direction => $arrow)
                <form method="POST" action="{{ route('admin.cms.menus.move', $item) }}">
                    @csrf <input type="hidden" name="direction" value="{{ $direction }}">
                    <button type="submit" class="size-8 cursor-pointer rounded-md text-slate-500 hover:bg-slate-100 disabled:cursor-default disabled:opacity-30" @disabled($direction === 'up' ? $isFirst : $isLast) aria-label="Move {{ $direction }}">{!! $arrow !!}</button>
                </form>
            @endforeach
            <a href="{{ route('admin.cms.menus.edit', $item) }}" class="ml-2 text-sm font-semibold text-brand-600 hover:text-brand-800">Edit</a>
        </div>
    @endif
</li>
