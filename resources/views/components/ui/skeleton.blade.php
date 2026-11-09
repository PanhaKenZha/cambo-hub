{{-- Loading placeholder. "cards" renders a grid of card shapes, "list" a stack of rows. --}}
@props(['variant' => 'cards', 'count' => 4, 'label' => null])
<div role="status" aria-live="polite" {{ $attributes }}>
    <span class="sr-only">{{ $label ?? __('Loading…') }}</span>
    @if($variant === 'list')
        <div class="space-y-3" aria-hidden="true">
            @for($i = 0; $i < $count; $i++)
                <div class="flex items-center gap-4 rounded-2xl border border-line bg-surface p-4">
                    <div class="skeleton size-14 shrink-0 rounded-xl"></div>
                    <div class="flex-1 space-y-2"><div class="skeleton h-3.5 w-2/3 rounded"></div><div class="skeleton h-3 w-1/2 rounded"></div></div>
                </div>
            @endfor
        </div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4" aria-hidden="true">
            @for($i = 0; $i < $count; $i++)
                <div class="overflow-hidden rounded-2xl border border-line bg-surface">
                    <div class="skeleton aspect-[4/3]"></div>
                    <div class="space-y-2 p-4"><div class="skeleton h-3.5 w-3/4 rounded"></div><div class="skeleton h-3 w-1/2 rounded"></div></div>
                </div>
            @endfor
        </div>
    @endif
</div>
