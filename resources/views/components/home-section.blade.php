{{-- Wrapper for one homepage section from Website › Homepage. $section is a SiteContent::homepageSections() row. --}}
@props(['section', 'compact' => false])
@php($headingId = 'section-'.$section['type'])
<section {{ $attributes->class(['home-section', 'py-8' => $compact, 'py-12 sm:py-16' => ! $compact]) }} aria-labelledby="{{ $headingId }}">
    <header class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <span class="mb-3 flex items-center gap-1.5" aria-hidden="true"><span class="h-1 w-8 rounded-full bg-primary"></span><span class="h-1 w-3 rounded-full bg-cambo-red"></span></span>
            <h2 id="{{ $headingId }}" class="text-2xl font-extrabold tracking-tight sm:text-[2rem]">{{ __($section['title']) }}</h2>
            @if(filled($section['subtitle']))<p class="mt-1.5 text-sm text-ink-muted sm:text-base">{{ __($section['subtitle']) }}</p>@endif
        </div>
        @if(filled($section['link_url']))
            <a href="{{ $section['link_url'] }}" class="group inline-flex shrink-0 items-center gap-2 rounded-full border border-line bg-surface px-4 py-2 text-sm font-semibold text-primary transition hover:border-primary hover:bg-primary hover:text-on-primary">{{ filled($section['button_label']) ? __($section['button_label']) : __('View all') }} <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span></a>
        @endif
    </header>
    {{ $slot }}
</section>
