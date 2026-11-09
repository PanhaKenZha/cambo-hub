@props(['section', 'compact' => false])
<section {{ $attributes->class(['border-t border-line', 'py-8' => $compact, 'py-12 sm:py-14' => ! $compact]) }} aria-labelledby="section-{{ $section->content_type->value }}">
    <header class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            @if($section->eyebrow)<x-ui.eyebrow>{{ __($section->eyebrow) }}</x-ui.eyebrow>@endif
            <h2 id="section-{{ $section->content_type->value }}" class="mt-2 text-2xl font-bold tracking-tight sm:text-[1.7rem]">{{ __($section->heading) }}</h2>
            @if($section->subheading)<p class="mt-1 text-sm text-ink-muted">{{ __($section->subheading) }}</p>@endif
        </div>
        @if($section->link_url && $section->link_label)
            <a href="{{ url($section->link_url) }}" class="group inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold text-primary">{{ __($section->link_label) }} <span aria-hidden="true" class="transition group-hover:translate-x-0.5">→</span></a>
        @endif
    </header>
    {{ $slot }}
</section>
