@props(['title' => null, 'description' => null])

<section {{ $attributes->class('rounded-2xl bg-surface shadow-card ring-1 ring-line') }}>
    @if($title)
        <header class="border-b border-line px-6 py-4">
            <h2 class="text-base font-semibold text-ink">{{ $title }}</h2>
            @if($description)
                <p class="mt-1 text-sm text-ink-muted">{{ $description }}</p>
            @endif
        </header>
    @endif
    <div class="p-6">{{ $slot }}</div>
</section>
