{{-- Hero from Website › Homepage: heading, description, optional button and image, plus the global search box. --}}
@php
    // A heading written as "First sentence. Second sentence." shows the second sentence highlighted on its own line.
    $heading = __($section['title']);
    $parts = preg_split('/(?<=[.!?។。])\s+/u', $heading, 2);
    // Compare file paths, not full URLs, so the check works on any host (localhost, 127.0.0.1, a real domain).
    $heroImagePath = ltrim(rawurldecode((string) parse_url((string) $section['image_url'], PHP_URL_PATH)), '/');
    $usesDefaultHero = $heroImagePath === '' || in_array($heroImagePath, [
        'images/backgrounds/cambodia-platform.webp',
        'images/Phnom Penh Recruitment Success Scene.png',
        'images/genomines/seedlings-closeup.webp',
        'frame_00001.webp',
    ], true);
    $heroImage = $usesDefaultHero ? asset('frame_00001.webp') : $section['image_url'];
    $heroAlt = $usesDefaultHero ? __('Aerial view of cultivated fields in the morning light') : __('Cambo Hub homepage feature image');
    $sequencePaths = $usesDefaultHero ? (glob(public_path('frame_*.webp')) ?: []) : [];
    natsort($sequencePaths);
    $sequenceFrames = array_values(array_map(
        fn (string $path): string => asset(basename($path)),
        $sequencePaths,
    ));
@endphp
<div class="hero-sequence" @if($sequenceFrames !== []) data-frame-sequence data-frames="{{ json_encode($sequenceFrames) }}" @endif>
<section id="chapter-hero" tabindex="-1" class="public-hero cinematic-hero relative isolate grid items-center lg:grid-cols-[1.1fr_1fr]" aria-labelledby="home-hero-title">
    <div class="animate-reveal hero-content">
        <span class="glass inline-flex items-center gap-2.5 rounded-full border border-line py-1.5 pr-4 pl-2 text-xs font-semibold text-ink shadow-sm">
            <span class="relative flex size-2.5" aria-hidden="true">
                <span class="absolute inline-flex size-full rounded-full bg-cambo-red animate-ping-soft"></span>
                <span class="relative inline-flex size-2.5 rounded-full bg-cambo-red"></span>
            </span>
            <span lang="km" class="font-khmer text-primary">សួស្តី</span>
            <span class="h-3 w-px bg-line-strong" aria-hidden="true"></span>
            {{ __('WELCOME TO CAMBO HUB') }}
        </span>
        <h1 id="home-hero-title" class="mt-6 text-4xl leading-[1.08] font-extrabold tracking-tight sm:text-5xl lg:text-[4rem]">{{ $parts[0] }}@isset($parts[1])<br><span class="text-gradient-brand">{{ $parts[1] }}</span>@endisset</h1>
        @if(filled($section['subtitle']))<p class="mt-6 max-w-xl text-base leading-relaxed text-ink-muted sm:text-lg">{{ __($section['subtitle']) }}</p>@endif

        @if($search['enabled'])
            <form action="{{ route('search') }}" method="get" role="search" class="glass group mt-8 flex max-w-xl items-center gap-2 rounded-2xl border border-line-strong p-1.5 pl-4 shadow-card transition focus-within:border-brand-500 focus-within:shadow-card-hover focus-within:ring-4 focus-within:ring-brand-500/15" data-loading>
                <label for="home-search" class="sr-only">{{ __('Search Cambo Hub') }}</label>
                <svg class="size-5 shrink-0 text-ink-subtle transition group-focus-within:scale-110 group-focus-within:text-primary" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input id="home-search" type="search" name="q" maxlength="100" placeholder="{{ __($search['placeholder'] ?: 'Search') }}" autocomplete="off" enterkeyhint="search" class="h-12 min-w-0 flex-1 bg-transparent text-sm text-ink placeholder:text-ink-subtle focus:outline-none">
                <x-ui.button class="h-12 shrink-0 rounded-xl px-6">{{ __('Search') }}</x-ui.button>
            </form>
            @if($search['suggestions'] !== [])
                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-ink-subtle">
                    <span>{{ __('Popular:') }}</span>
                    @foreach($search['suggestions'] as $suggestion)
                        <a href="{{ route('search', ['q' => $suggestion]) }}" class="rounded-full border border-line bg-surface/70 px-3 py-1 font-medium text-ink-muted backdrop-blur transition hover:-translate-y-0.5 hover:border-primary hover:text-primary">{{ $suggestion }}</a>
                    @endforeach
                </div>
            @endif
        @endif

        <div class="mt-8 flex flex-wrap items-center gap-3">
            @if(filled($section['link_url']) && filled($section['button_label']))
                <x-ui.button :href="$section['link_url']" size="lg" class="group">{{ __($section['button_label']) }} <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span></x-ui.button>
            @endif
            @auth
                <x-ui.button :href="route('dashboard')" variant="secondary" size="lg" class="group">{{ __('My dashboard') }} <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span></x-ui.button>
            @else
                @if($registrationOpen)<x-ui.button :href="route('register')" variant="secondary" size="lg" class="group">{{ __('Create your account') }} <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span></x-ui.button>@endif
                <a href="{{ route('login') }}" class="px-2 text-sm font-semibold text-primary hover:underline">{{ __('User sign in') }}</a>
            @endauth
        </div>
    </div>

    <figure class="home-hero-visual relative min-w-0">
        <div class="skeleton overflow-hidden rounded-[1.75rem] bg-surface-muted">
            <img src="{{ $heroImage }}" width="1920" height="1080" alt="{{ $heroAlt }}" fetchpriority="high" data-placeholder @if($sequenceFrames !== []) data-frame-image @endif class="aspect-video w-full object-cover">
        </div>
        <figcaption class="home-hero-caption text-white">
            <strong class="block text-2xl font-bold tracking-tight">{{ __('Cambodia, connected.') }}</strong>
            <span class="mt-2 block text-xs leading-relaxed text-white/80">{{ __('One account. Everyday possibilities.') }}</span>
        </figcaption>
    </figure>
    @if($sequenceFrames !== [])
        @include('home.sections.hero-growth')
    @endif
    <div class="hero-scene-label" aria-hidden="true">
        <span class="hero-scene-dot"></span>
        <span>{{ __('Rooted in Cambodia. Open to possibility.') }}</span>
    </div>
    <a href="#growth-introduction" class="hero-scroll-cue">
        <span>{{ __('Scroll to explore') }}</span>
        <span class="hero-scroll-track" aria-hidden="true"></span>
    </a>
</section>
</div>
