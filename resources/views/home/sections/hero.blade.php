{{-- Hero from Website › Homepage: heading, description, optional button and image, plus the global search box. --}}
@php
    // A heading written as "First sentence. Second sentence." shows the second sentence highlighted on its own line.
    $heading = __($section['title']);
    $parts = preg_split('/(?<=[.!?។。])\s+/u', $heading, 2);
@endphp
<section class="grid items-center gap-12 py-10 sm:py-14 lg:grid-cols-[1.15fr_1fr] lg:gap-16 lg:py-20" aria-labelledby="home-hero-title">
    <div class="animate-reveal">
        <x-ui.eyebrow>{{ __('WELCOME TO CAMBO HUB') }}</x-ui.eyebrow>
        <h1 id="home-hero-title" class="mt-4 text-4xl leading-[1.15] font-bold tracking-tight sm:text-5xl lg:text-[3.4rem]">{{ $parts[0] }}@isset($parts[1])<br><span class="text-primary">{{ $parts[1] }}</span>@endisset</h1>
        @if(filled($section['subtitle']))<p class="mt-5 max-w-xl text-base leading-relaxed text-ink-muted">{{ __($section['subtitle']) }}</p>@endif

        @if($search['enabled'])
            <form action="{{ route('search') }}" method="get" role="search" class="mt-8 flex max-w-xl items-center gap-2 rounded-2xl border border-line-strong bg-surface p-1.5 pl-4 shadow-card transition focus-within:border-brand-500 focus-within:ring-3 focus-within:ring-brand-500/20" data-loading>
                <label for="home-search" class="sr-only">{{ __('Search Cambo Hub') }}</label>
                <svg class="size-5 shrink-0 text-ink-subtle" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input id="home-search" type="search" name="q" maxlength="100" placeholder="{{ __($search['placeholder'] ?: 'Search') }}" autocomplete="off" enterkeyhint="search" class="h-11 min-w-0 flex-1 bg-transparent text-sm text-ink placeholder:text-ink-subtle focus:outline-none">
                <x-ui.button class="shrink-0">{{ __('Search') }}</x-ui.button>
            </form>
            @if($search['suggestions'] !== [])
                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-ink-subtle">
                    <span>{{ __('Popular:') }}</span>
                    @foreach($search['suggestions'] as $suggestion)
                        <a href="{{ route('search', ['q' => $suggestion]) }}" class="rounded-full border border-line bg-surface px-3 py-1 font-medium text-ink-muted transition hover:border-primary hover:text-primary">{{ $suggestion }}</a>
                    @endforeach
                </div>
            @endif
        @endif

        <div class="mt-8 flex flex-wrap items-center gap-3">
            @if(filled($section['link_url']) && filled($section['button_label']))
                <x-ui.button :href="$section['link_url']">{{ __($section['button_label']) }} <span aria-hidden="true">→</span></x-ui.button>
            @endif
            @auth
                <x-ui.button :href="route('dashboard')" variant="secondary">{{ __('My dashboard') }} <span aria-hidden="true">→</span></x-ui.button>
            @else
                @if($registrationOpen)<x-ui.button :href="route('register')" variant="secondary">{{ __('Create your account') }} <span aria-hidden="true">→</span></x-ui.button>@endif
                <a href="{{ route('login') }}" class="px-2 text-sm font-semibold text-primary hover:underline">{{ __('User sign in') }}</a>
            @endauth
        </div>
    </div>

    <figure class="relative mx-auto w-full max-w-lg animate-reveal [animation-delay:80ms]">
        <div class="absolute inset-0 -z-10 -rotate-3 rounded-[2rem] bg-primary-soft" aria-hidden="true"></div>
        <div class="skeleton overflow-hidden rounded-[1.75rem]">
            <img src="{{ $section['image_url'] ?? asset('images/backgrounds/cambodia-platform.webp') }}" width="387" height="386" alt="{{ __('Angkor Wat and a Cambodian city connected by blue and red light trails') }}" fetchpriority="high" data-placeholder class="aspect-square w-full object-cover">
        </div>
        <figcaption class="absolute -bottom-5 left-4 right-4 flex items-center gap-3 rounded-2xl border border-line bg-surface-raised p-4 shadow-card sm:-left-6 sm:right-8">
            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-icon-tile"><img src="{{ asset('images/branding/cambo-hub-mark.svg') }}" width="28" height="28" alt=""></span>
            <span><strong class="block text-sm">{{ __('Cambodia, connected.') }}</strong><small class="text-[10px] tracking-[0.14em] text-ink-subtle uppercase">{{ __('YOUR ALL-IN-ONE PLATFORM') }}</small></span>
        </figcaption>
    </figure>
</section>
