@extends('layouts.public')
@inject('cms', 'App\Services\CmsService')
@section('title', __('Home'))
@section('description', __('Discover Cambo Hub, a connected platform for Cambodian opportunities, businesses, learning, and community.'))
@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <section class="grid items-center gap-12 py-10 sm:py-14 lg:grid-cols-[1.15fr_1fr] lg:gap-16 lg:py-20" aria-labelledby="home-hero-title">
        <div class="animate-reveal">
            @if(! empty($hero['eyebrow']))<x-ui.eyebrow>{{ __($hero['eyebrow']) }}</x-ui.eyebrow>@endif
            <h1 id="home-hero-title" class="mt-4 text-4xl leading-[1.15] font-bold tracking-tight sm:text-5xl lg:text-[3.4rem]">{{ __($hero['title'] ?? '') }}@if(! empty($hero['highlight']))<br><span class="text-primary">{{ __($hero['highlight']) }}</span>@endif</h1>
            @if(! empty($hero['intro']))<p class="mt-5 max-w-xl text-base leading-relaxed text-ink-muted">{{ __($hero['intro']) }}</p>@endif

            <form action="{{ route('search') }}" method="get" role="search" class="mt-8 flex max-w-xl items-center gap-2 rounded-2xl border border-line-strong bg-surface p-1.5 pl-4 shadow-card transition focus-within:border-brand-500 focus-within:ring-3 focus-within:ring-brand-500/20" data-loading>
                <label for="home-search" class="sr-only">{{ __('Search Cambo Hub') }}</label>
                <svg class="size-5 shrink-0 text-ink-subtle" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input id="home-search" type="search" name="q" maxlength="100" placeholder="{{ __($search['placeholder'] ?? 'Search') }}" autocomplete="off" enterkeyhint="search" class="h-11 min-w-0 flex-1 bg-transparent text-sm text-ink placeholder:text-ink-subtle focus:outline-none">
                <x-ui.button class="shrink-0">{{ __('Search') }}</x-ui.button>
            </form>
            @if(! empty($search['suggestions']))
                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-ink-subtle">
                    <span>{{ __('Popular:') }}</span>
                    @foreach($search['suggestions'] as $suggestion)
                        <a href="{{ route('search', ['q' => $suggestion]) }}" class="rounded-full border border-line bg-surface px-3 py-1 font-medium text-ink-muted transition hover:border-primary hover:text-primary">{{ $suggestion }}</a>
                    @endforeach
                </div>
            @endif

            <div class="mt-8 flex flex-wrap items-center gap-3">
                @auth
                    <x-ui.button :href="route('dashboard')" variant="secondary">{{ __('My dashboard') }} <span aria-hidden="true">→</span></x-ui.button>
                @else
                    <x-ui.button :href="route('register')" variant="secondary">{{ __('Create your account') }} <span aria-hidden="true">→</span></x-ui.button>
                    <a href="{{ route('login') }}" class="px-2 text-sm font-semibold text-primary hover:underline">{{ __('User sign in') }}</a>
                @endauth
            </div>
        </div>

        @if(! empty($hero['image']))
            <figure class="relative mx-auto w-full max-w-lg animate-reveal [animation-delay:80ms]">
                <div class="absolute inset-0 -z-10 -rotate-3 rounded-[2rem] bg-primary-soft" aria-hidden="true"></div>
                <div class="skeleton overflow-hidden rounded-[1.75rem]">
                    <img src="{{ asset($hero['image']) }}" width="387" height="386" alt="{{ __($hero['image_alt'] ?? '') }}" fetchpriority="high" data-placeholder class="aspect-square w-full object-cover">
                </div>
                @if(! empty($hero['caption_title']))
                    <figcaption class="absolute -bottom-5 left-4 right-4 flex items-center gap-3 rounded-2xl border border-line bg-surface-raised p-4 shadow-card sm:-left-6 sm:right-8">
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-icon-tile"><img src="{{ asset($cms->setting('branding.logo', 'images/branding/cambo-hub-mark.svg')) }}" width="28" height="28" alt=""></span>
                        <span><strong class="block text-sm">{{ __($hero['caption_title']) }}</strong><small class="text-[10px] tracking-[0.14em] text-ink-subtle uppercase">{{ __($hero['caption_label'] ?? '') }}</small></span>
                    </figcaption>
                @endif
            </figure>
        @endif
    </section>

    @forelse($blocks as $block)
        @include('home.sections.'.$block['section']->content_type->value, ['section' => $block['section'], 'items' => $block['items']])
    @empty
        <div class="border-t border-line py-12">
            <x-ui.empty-state image="images/content/empty-state.webp" :title="__('Fresh content is on its way')" :description="__('Check back soon for the latest news, jobs, products, and events.')" />
        </div>
    @endforelse

    @if(! empty($membership['heading']))
        <div class="mt-4">
            @include('partials.cta-band', [
                'eyebrow' => __($membership['eyebrow'] ?? ''),
                'heading' => __($membership['heading']),
                'body' => __($membership['body'] ?? ''),
                'primaryUrl' => auth()->check() ? route('dashboard') : route('register'),
                'primaryLabel' => auth()->check() ? __('Go to your dashboard') : __($membership['register_label'] ?? 'Create your account'),
                'secondaryUrl' => auth()->check() ? null : route('login'),
                'secondaryLabel' => __($membership['login_label'] ?? 'User sign in'),
            ])
        </div>
    @endif
</div>
@endsection
