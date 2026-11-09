@inject('cms', 'App\Services\CmsService')
@php
    $branding = $cms->setting('branding', []);
    $footer = $cms->setting('footer', []);
    $siteName = $branding['site_name'] ?? 'Cambo Hub';
    $logo = asset($branding['logo'] ?? 'images/branding/cambo-hub-mark.svg');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-ui.document-head
        :title="html_entity_decode(trim($__env->yieldContent('title')), ENT_QUOTES).' · '.$siteName"
        :description="html_entity_decode(trim($__env->yieldContent('description')), ENT_QUOTES) ?: null"
        :khmer-text="($branding['tagline'] ?? '').'ខ្មែរ'">
        @stack('head')
    </x-ui.document-head>
</head>
<body class="flex min-h-svh flex-col bg-canvas text-ink">
<a href="#main-content" class="fixed top-3 left-3 z-[60] -translate-y-24 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary shadow-card transition focus:translate-y-0">{{ __('Skip to content') }}</a>

<header class="sticky top-0 z-40 border-b border-line bg-surface/90 backdrop-blur-md supports-[backdrop-filter]:bg-surface/80">
    <div class="mx-auto flex h-16 max-w-7xl items-center gap-3 px-4 sm:h-[4.5rem] sm:px-6 lg:gap-6 lg:px-8">
        <a href="{{ route('home') }}" class="mr-auto flex min-w-0 items-center gap-2.5 rounded-xl lg:mr-0" aria-label="{{ __('Cambo Hub home') }}">
            <img src="{{ $logo }}" width="44" height="44" alt="" class="size-9 shrink-0 sm:size-11">
            <span class="min-w-0 leading-none">
                <span class="block text-xl font-extrabold tracking-tight text-brand-700 sm:text-[1.6rem] dark:text-white">{{ $branding['name'] ?? '' }}<span class="text-cambo-red">{{ $branding['name_accent'] ?? '' }}</span></span>
                @if(! empty($branding['tagline']))
                    <small lang="{{ $branding['tagline_locale'] ?? app()->getLocale() }}" class="mt-1 hidden truncate text-[10px] font-medium text-ink-muted min-[420px]:block">{{ $branding['tagline'] }}</small>
                @endif
            </span>
        </a>

        <nav class="hidden flex-1 items-center justify-center gap-1 lg:flex" aria-label="{{ __('Main navigation') }}">
            <x-public-nav-links />
        </nav>

        <div class="flex items-center gap-2" aria-label="{{ __('User account') }}">
            <a href="{{ route('search') }}" class="hidden size-10 place-items-center rounded-xl border border-line bg-surface text-ink-muted transition hover:border-primary hover:text-primary sm:grid" title="{{ __('Search') }}">
                <span class="sr-only">{{ __('Search Cambo Hub') }}</span>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
            </a>
            <x-language-switcher />
            <x-ui.theme-toggle />
            @auth
                <div class="relative hidden lg:block">
                    <button type="button" data-disclosure aria-controls="account-menu" aria-expanded="false" class="flex h-10 cursor-pointer items-center gap-2 rounded-xl bg-primary pr-3 pl-1.5 text-sm font-semibold text-on-primary transition hover:bg-primary-hover">
                        <span class="grid size-7 place-items-center rounded-lg bg-white/20 text-xs" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="max-w-28 truncate">{{ __('My account') }}</span>
                    </button>
                    <div id="account-menu" hidden class="absolute right-0 z-50 mt-2 w-64 rounded-2xl border border-line bg-surface-raised p-1.5 shadow-card-hover">
                        <div class="border-b border-line px-3 py-2.5">
                            <p class="truncate text-sm font-semibold text-ink">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-ink-subtle">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="{{ route('dashboard') }}" class="mt-1 block rounded-xl px-3 py-2.5 text-sm text-ink hover:bg-surface-muted">{{ __('My dashboard') }}</a>
                        <a href="{{ route('account.edit') }}" class="block rounded-xl px-3 py-2.5 text-sm text-ink hover:bg-surface-muted">{{ __('Account settings') }}</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="w-full cursor-pointer rounded-xl px-3 py-2.5 text-left text-sm text-danger hover:bg-danger-soft">{{ __('Sign out') }}</button></form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="hidden h-10 items-center rounded-xl px-3 text-sm font-semibold text-primary transition hover:bg-primary-soft lg:inline-flex">{{ __('User sign in') }}</a>
                <x-ui.button :href="route('register')" size="sm" class="hidden h-10 sm:inline-flex">{{ __('Create account') }}</x-ui.button>
            @endauth
            <button type="button" data-disclosure aria-controls="mobile-menu" aria-expanded="false" class="grid size-10 cursor-pointer place-items-center rounded-xl border border-line bg-surface text-ink transition hover:border-primary lg:hidden">
                <span class="sr-only">{{ __('Main navigation') }}</span>
                <svg class="size-5 [[aria-expanded=true]>&]:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                <svg class="hidden size-5 [[aria-expanded=true]>&]:block" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <div id="mobile-menu" hidden class="border-t border-line bg-surface lg:hidden">
        <nav class="mx-auto grid max-w-7xl gap-1 px-4 py-4 sm:grid-cols-2 sm:px-6" aria-label="{{ __('Main navigation') }}">
            <x-public-nav-links layout="mobile" />
            <a href="{{ route('search') }}" class="flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-medium text-ink hover:bg-surface-muted sm:hidden"><x-ui.icon name="search" :size="18" class="size-[18px] opacity-70" />{{ __('Search') }}</a>
        </nav>
        <div class="mx-auto flex max-w-7xl flex-wrap gap-2 border-t border-line px-4 py-4 sm:px-6">
            @auth
                <x-ui.button :href="route('dashboard')" size="sm">{{ __('My dashboard') }}</x-ui.button>
                <x-ui.button :href="route('account.edit')" variant="secondary" size="sm">{{ __('Account settings') }}</x-ui.button>
                <form method="POST" action="{{ route('logout') }}">@csrf<x-ui.button variant="ghost" size="sm" class="text-danger!">{{ __('Sign out') }}</x-ui.button></form>
            @else
                <x-ui.button :href="route('register')" size="sm">{{ __('Create account') }}</x-ui.button>
                <x-ui.button :href="route('login')" variant="secondary" size="sm">{{ __('User sign in') }}</x-ui.button>
            @endauth
        </div>
    </div>
</header>

<main id="main-content" tabindex="-1" class="flex-1 focus:outline-none">@yield('content')</main>

<footer class="border-t border-line bg-surface">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1fr_2fr] lg:px-8">
        <div class="max-w-xs">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 rounded-xl">
                <img src="{{ $logo }}" width="36" height="36" alt="" class="size-9" loading="lazy">
                <span class="text-2xl font-extrabold tracking-tight text-brand-700 dark:text-white">{{ $branding['name'] ?? '' }}<span class="text-cambo-red">{{ $branding['name_accent'] ?? '' }}</span></span>
            </a>
            <p class="mt-3 text-sm leading-relaxed text-ink-muted">@foreach($footer['tagline'] ?? [] as $line){{ __($line) }}@if(! $loop->last)<br>@endif @endforeach</p>
        </div>
        <div class="grid grid-cols-2 gap-8 sm:grid-cols-4">
            @foreach($footer['columns'] ?? [] as $column)
                <nav aria-label="{{ __($column['heading']) }}">
                    <h2 class="text-sm font-semibold text-ink">{{ __($column['heading']) }}</h2>
                    <ul class="mt-3 space-y-2">
                        @foreach($column['links'] ?? [] as $link)
                            <li><a href="{{ $cms->linkUrl($link) }}" class="text-sm text-ink-muted transition hover:text-primary">{{ __($link['label']) }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endforeach
            <nav aria-label="{{ __('Account') }}">
                <h2 class="text-sm font-semibold text-ink">{{ __('Account') }}</h2>
                <ul class="mt-3 space-y-2">
                    @auth
                        <li><a href="{{ route('dashboard') }}" class="text-sm text-ink-muted transition hover:text-primary">{{ __('My dashboard') }}</a></li>
                        <li><a href="{{ route('account.edit') }}" class="text-sm text-ink-muted transition hover:text-primary">{{ __('Account settings') }}</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="text-sm text-ink-muted transition hover:text-primary">{{ __('User sign in') }}</a></li>
                        <li><a href="{{ route('register') }}" class="text-sm text-ink-muted transition hover:text-primary">{{ __('Create an account') }}</a></li>
                    @endauth
                </ul>
            </nav>
        </div>
    </div>
    <div class="border-t border-line">
        <div class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-5 text-xs text-ink-subtle sm:flex-row sm:justify-between sm:px-6 lg:px-8">
            <span>© {{ date('Y') }} {{ __($footer['copyright'] ?? '') }}</span>
            <span>{{ __($footer['note'] ?? '') }}</span>
        </div>
    </div>
</footer>
</body>
</html>
