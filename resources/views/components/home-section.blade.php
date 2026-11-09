{{-- Wrapper for one homepage section from Website › Homepage. $section is a SiteContent::homepageSections() row. --}}
@props(['section', 'compact' => false])
@php($headingId = 'section-'.$section['type'])
<section {{ $attributes->class(['border-t border-line', 'py-8' => $compact, 'py-12 sm:py-14' => ! $compact]) }} aria-labelledby="{{ $headingId }}">
    <header class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 id="{{ $headingId }}" class="text-2xl font-bold tracking-tight sm:text-[1.7rem]">{{ __($section['title']) }}</h2>
            @if(filled($section['subtitle']))<p class="mt-1 text-sm text-ink-muted">{{ __($section['subtitle']) }}</p>@endif
        </div>
        @if(filled($section['link_url']))
            <a href="{{ $section['link_url'] }}" class="group inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold text-primary">{{ filled($section['button_label']) ? __($section['button_label']) : __('View all') }} <span aria-hidden="true" class="transition group-hover:translate-x-0.5">→</span></a>
        @endif
    </header>
    {{ $slot }}
</section>
