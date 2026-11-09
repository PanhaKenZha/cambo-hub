@extends('layouts.public')
@php($purpose = config('purpose.'.$page))
@section('title', __($purpose['label']))
@section('description', __($purpose['intro']))
@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <section class="grid items-center gap-12 py-12 lg:grid-cols-[1.15fr_1fr] lg:gap-16 lg:py-20">
        <div class="animate-reveal">
            <x-ui.eyebrow>{{ __($purpose['eyebrow']) }}</x-ui.eyebrow>
            <nav class="mt-5 inline-flex rounded-xl border border-line bg-surface-muted p-1" aria-label="{{ __('About Cambo Hub') }}">
                @foreach(['mission' => 'Our mission', 'vision' => 'Our vision'] as $routeName => $label)
                    <a href="{{ route($routeName) }}" @if($page === $routeName) aria-current="page" @endif class="rounded-lg px-4 py-1.5 text-xs font-semibold text-ink-muted transition hover:text-ink aria-[current=page]:bg-surface aria-[current=page]:text-primary aria-[current=page]:shadow-sm">{{ __($label) }}</a>
                @endforeach
            </nav>
            <h1 class="mt-6 text-4xl leading-tight font-bold tracking-tight sm:text-5xl">{{ __($purpose['heading']) }}<br><span class="text-primary">{{ __($purpose['highlight']) }}</span></h1>
            <p class="mt-5 max-w-xl text-base leading-relaxed text-ink-muted">{{ __($purpose['intro']) }}</p>
            <div class="mt-8 flex flex-wrap items-center gap-4">
                <x-ui.button :href="route($purpose['other'])" size="lg">{{ __($purpose['other_label']) }} <span aria-hidden="true">→</span></x-ui.button>
                <a href="#our-purpose" class="text-sm font-semibold text-primary hover:underline">{{ __('See what drives us') }} <span aria-hidden="true">↓</span></a>
            </div>
            <p class="mt-8 flex items-center gap-3 text-xs text-ink-subtle"><span class="h-0.5 w-8 bg-gradient-to-r from-brand-700 from-60% to-cambo-red to-60%" aria-hidden="true"></span>{{ __('One account. Everyday possibilities.') }}</p>
        </div>
        <figure class="relative mx-auto w-full max-w-lg animate-reveal [animation-delay:80ms]">
            <div class="absolute inset-0 -z-10 -rotate-3 rounded-[2rem] bg-primary-soft" aria-hidden="true"></div>
            <div class="skeleton overflow-hidden rounded-[1.75rem]">
                <img src="{{ asset($purpose['hero']) }}" width="387" height="386" alt="{{ __($purpose['hero_alt']) }}" fetchpriority="high" data-placeholder class="aspect-square w-full object-cover">
            </div>
            <figcaption class="absolute -bottom-5 left-4 right-4 flex items-center gap-3 rounded-2xl border border-line bg-surface-raised p-4 shadow-card sm:-left-6 sm:right-8">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-icon-tile"><img src="{{ asset('images/branding/cambo-hub-mark.svg') }}" width="28" height="28" alt=""></span>
                <span><strong class="block text-sm">{{ __($purpose['image_label']) }}</strong><small class="text-[10px] tracking-[0.14em] text-ink-subtle uppercase">{{ __('THE CAMBO HUB PURPOSE') }}</small></span>
            </figcaption>
        </figure>
    </section>

    <section id="our-purpose" class="grid gap-6 border-t border-line py-12 lg:grid-cols-2 lg:gap-16">
        <div><x-ui.eyebrow>{{ __($purpose['label']) }}</x-ui.eyebrow><h2 class="mt-3 text-3xl leading-snug font-bold tracking-tight">{{ __($purpose['statement']) }}</h2></div>
        <p class="self-center text-base leading-relaxed text-ink-muted">{{ __($purpose['statement_body']) }}</p>
    </section>

    <section class="grid gap-5 pb-14 md:grid-cols-3" aria-label="{{ __('Our priorities') }}">
        @foreach($purpose['pillars'] as $pillar)
            <x-ui.card as="article" padding="p-6" interactive>
                <div class="flex items-start justify-between">
                    <span class="grid size-12 place-items-center rounded-xl bg-icon-tile"><img src="{{ asset('images/icons/'.$pillar['icon'].'-blue.svg') }}" width="26" height="26" alt="" loading="lazy"></span>
                    <span class="text-xs font-medium tracking-wider text-ink-subtle">0{{ $loop->iteration }}</span>
                </div>
                <h3 class="mt-5 text-base font-semibold">{{ __($pillar['title']) }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-ink-muted">{{ __($pillar['body']) }}</p>
            </x-ui.card>
        @endforeach
    </section>

    <section class="grid items-center gap-10 pb-14 lg:grid-cols-2 lg:gap-16">
        <figure class="overflow-hidden rounded-3xl bg-primary-soft">
            <div class="skeleton"><img src="{{ asset($purpose['story_image']) }}" width="508" height="337" alt="{{ __($purpose['story_alt']) }}" loading="lazy" data-placeholder class="aspect-[3/2] w-full object-contain p-4"></div>
            <figcaption class="flex items-center gap-2 border-t border-line px-6 py-4 text-sm text-ink-muted"><span class="text-cambo-red" aria-hidden="true">✦</span> {{ __('People at the heart of progress.') }}</figcaption>
        </figure>
        <div>
            <x-ui.eyebrow>{{ __($purpose['story_label']) }}</x-ui.eyebrow>
            <h2 class="mt-3 text-3xl leading-snug font-bold tracking-tight">{{ __($purpose['story_heading']) }}</h2>
            <p class="mt-4 text-base leading-relaxed text-ink-muted">{{ __($purpose['story_body']) }}</p>
            <ol class="mt-6 space-y-4">
                @foreach($purpose['principles'] as $principle)
                    <li class="flex gap-4">
                        <span class="mt-0.5 h-fit shrink-0 rounded-lg bg-primary-soft px-2 py-1 text-[11px] font-semibold text-primary">0{{ $loop->iteration }}</span>
                        <div><h3 class="text-sm font-semibold">{{ __($principle['title']) }}</h3><p class="mt-0.5 text-sm text-ink-muted">{{ __($principle['body']) }}</p></div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="border-t border-line py-12 text-center" aria-labelledby="services-heading">
        <x-ui.eyebrow>{{ __('MANY POSSIBILITIES. ONE CAMBO HUB.') }}</x-ui.eyebrow>
        <h2 id="services-heading" class="mt-3 text-3xl font-bold tracking-tight">{{ __('A purpose that connects every service.') }}</h2>
        <p class="mt-2 text-sm text-ink-muted">{{ __('Our platform brings together the things that matter in everyday life.') }}</p>
        <ul class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8">
            @foreach(['news' => 'News', 'jobs' => 'Jobs', 'shopping' => 'Shopping', 'business' => 'Business', 'education' => 'Education', 'social' => 'Community', 'hosting' => 'Hosting', 'events' => 'Events'] as $icon => $name)
                <li class="flex flex-col items-center gap-3 rounded-2xl border border-line bg-surface px-2 py-5 text-xs font-medium"><img src="{{ asset('images/icons/'.$icon.'-blue.svg') }}" width="26" height="26" alt="" loading="lazy" class="icon-adapt"><span>{{ __($name) }}</span></li>
            @endforeach
        </ul>
    </section>

    @include('partials.cta-band', [
        'eyebrow' => __('LET’S MOVE FORWARD, TOGETHER'),
        'heading' => __('Your next chapter').' '.__('belongs here.'),
        'body' => __('Be part of a more connected Cambodia.'),
        'primaryUrl' => auth()->check() ? route('dashboard') : route('register'),
        'primaryLabel' => auth()->check() ? __('Go to your dashboard') : __('Join Cambo Hub'),
        'secondaryUrl' => route($purpose['other']),
        'secondaryLabel' => __($purpose['other_label']),
    ])
</div>
@endsection
