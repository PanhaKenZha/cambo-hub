@props(['title', 'description' => null, 'image' => null, 'icon' => null])
<div {{ $attributes->class(['flex flex-col items-center rounded-2xl border border-dashed border-line-strong bg-surface px-6 py-12 text-center']) }}>
    @if($image)
        <img src="{{ asset($image) }}" width="180" height="180" alt="" loading="lazy" class="mb-4 size-36 object-contain sm:size-44 dark:opacity-90">
    @elseif($icon)
        <span class="mb-4 grid size-14 place-items-center rounded-2xl bg-icon-tile">
            <img src="{{ asset('images/icons/'.$icon.'-blue.svg') }}" width="28" height="28" alt="" class="size-7">
        </span>
    @endif
    <h2 class="text-lg font-semibold text-ink">{{ $title }}</h2>
    @if($description)<p class="mt-1 max-w-md text-sm text-ink-muted">{{ $description }}</p>@endif
    @if($slot->isNotEmpty())<div class="mt-5 flex flex-wrap justify-center gap-3">{{ $slot }}</div>@endif
</div>
