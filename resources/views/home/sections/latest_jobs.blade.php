<x-home-section :section="$section">
    <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($items as $item)
            <li>
                <x-ui.card as="article" padding="p-5" interactive class="flex h-full flex-col gap-4">
                    <div class="flex items-start gap-3">
                        <img src="{{ $item->imageUrl('images/placeholders/company.svg') }}" width="48" height="48" alt="" loading="lazy" class="size-12 shrink-0 rounded-xl border border-line bg-surface-muted object-cover">
                        <div class="min-w-0">
                            <h3 class="text-base leading-snug font-semibold">@if($item->linkUrl())<a href="{{ $item->linkUrl() }}" class="stretched-link">{{ $item->title }}</a>@else{{ $item->title }}@endif</h3>
                            @if($item->detail('company'))<p class="truncate text-sm text-ink-muted">{{ $item->detail('company') }}</p>@endif
                        </div>
                    </div>
                    <ul class="flex flex-wrap gap-x-4 gap-y-1.5 text-xs text-ink-muted">
                        @if($item->detail('location'))<li class="flex items-center gap-1.5"><x-ui.icon name="location" :size="14" />{{ $item->detail('location') }}</li>@endif
                        @if($item->detail('employment_type'))<li class="flex items-center gap-1.5"><x-ui.icon name="jobs" :size="14" />{{ $item->detail('employment_type') }}</li>@endif
                    </ul>
                    <div class="mt-auto flex items-center justify-between gap-3 border-t border-line pt-4">
                        @if($item->detail('salary'))<strong class="text-sm text-primary">{{ $item->detail('salary') }}</strong>@endif
                        @if($item->badge)<x-ui.badge variant="accent">{{ $item->badge }}</x-ui.badge>@endif
                    </div>
                </x-ui.card>
            </li>
        @endforeach
    </ul>
</x-home-section>
