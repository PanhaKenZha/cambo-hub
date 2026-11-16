@inject('siteContent', 'App\Services\SiteContent')
@inject('platformSettings', 'App\Services\PlatformSettings')
@php
    $siteName = $platformSettings->get('general.site_name', 'Cambo Hub');
    $tagline = $platformSettings->get('general.tagline');
    // "Cambo Hub" renders as Cambo + red "Hub"; single-word names render in one colour.
    $nameWords = preg_split('/\s+/', trim($siteName));
    $nameAccent = count($nameWords) > 1 ? array_pop($nameWords) : '';
    $nameMain = implode(' ', $nameWords);
    $logo = asset('images/branding/cambo-hub-mark.svg');
    $headerMenu = $siteContent->menu(\App\Enums\MenuLocation::Header);
    $footer = $siteContent->footer();
    $announcements = $siteContent->announcements();
    $registrationOpen = (bool) $platformSettings->get('registration.enabled', true);
    $searchEnabled = (bool) $platformSettings->get('search.enabled', true);
    $currentUrl = url()->full();
    $isCurrent = fn (?string $url): bool => filled($url) && $url !== '#' && rtrim(url($url), '/') === rtrim($currentUrl, '/');
    // Menu links are stored as "/path" or full URLs; site paths are made absolute like every other link.
    $href = fn (?string $url): string => blank($url) ? '#' : (str_starts_with($url, '/') ? url($url) : $url);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-ui.document-head
        :title="html_entity_decode(trim($__env->yieldContent('title')), ENT_QUOTES).' · '.$siteName"
        :description="(html_entity_decode(trim($__env->yieldContent('description')), ENT_QUOTES) ?: $platformSettings->get('seo.meta_description')) ?: null"
        :robots="$platformSettings->get('seo.allow_indexing', true) ? null : 'noindex, nofollow'"
        khmer-text="ខ្មែរ">
        @stack('head')
    </x-ui.document-head>
</head>
<body class="public-site flex min-h-svh flex-col bg-canvas text-ink">
<div class="ambient-background" aria-hidden="true"><span></span><span></span><span></span></div>
<a href="#main-content" class="fixed top-3 left-3 z-[60] -translate-y-24 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary shadow-card transition focus:translate-y-0">{{ __('Skip to content') }}</a>

@foreach($announcements as $announcement)
    <div data-announcement="{{ $announcement['id'] }}" role="{{ $announcement['level'] === 'critical' ? 'alert' : 'status' }}" @class([
        'border-b text-sm',
        'border-primary/20 bg-primary-soft text-primary' => $announcement['level'] === 'info',
        'border-warning/30 bg-warning-soft text-warning' => $announcement['level'] === 'warning',
        'border-danger/30 bg-danger-soft text-danger' => ! in_array($announcement['level'], ['info', 'warning'], true),
    ])>
        <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-2.5 sm:px-6 lg:px-8">
            <p class="flex-1 font-medium">
                {{ $announcement['message'] }}
                @if($announcement['link_url'])<a href="{{ $announcement['link_url'] }}" class="ml-1 font-semibold underline underline-offset-2">{{ $announcement['link_label'] ?: __('Learn more') }}</a>@endif
            </p>
            @if($announcement['dismissible'])
                <button type="button" data-dismiss-announcement class="grid size-7 shrink-0 cursor-pointer place-items-center rounded-lg hover:bg-black/5 dark:hover:bg-white/10">
                    <span class="sr-only">{{ __('Dismiss') }}</span>
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/></svg>
                </button>
            @endif
        </div>
    </div>
@endforeach

<div class="pointer-events-none fixed inset-x-0 top-0 z-55 h-0.75" aria-hidden="true"><div data-scroll-progress class="h-full"></div></div>

<header data-site-header class="sticky top-0 z-40 px-2 pt-2 transition-[padding] duration-300 sm:px-4 sm:pt-3">
    <div data-header-bar class="glass mx-auto max-w-7xl rounded-2xl border border-line/80 shadow-sm transition-shadow duration-300">
    <div class="flex h-16 items-center gap-3 px-3 sm:px-5 lg:gap-5">
        <a href="{{ route('home') }}" class="mr-auto flex min-w-0 shrink-0 items-center gap-2.5 rounded-xl xl:mr-0" aria-label="{{ __('Cambo Hub home') }}">
            <img src="{{ $logo }}" width="44" height="44" alt="" class="size-9 shrink-0 transition duration-500 hover:rotate-[360deg] sm:size-10">
            <span class="min-w-0 leading-none">
                <span class="block text-xl font-extrabold tracking-tight text-brand-700 sm:text-2xl dark:text-white">{{ $nameMain }}<span class="text-cambo-red">{{ $nameAccent }}</span></span>
                @if(filled($tagline))<small class="mt-1 hidden truncate text-[10px] font-medium text-ink-muted min-[420px]:block">{{ __($tagline) }}</small>@endif
            </span>
        </a>

        <nav class="hidden flex-1 items-center justify-center gap-0.5 xl:flex" aria-label="{{ __('Main navigation') }}">
            <x-public-nav-links :items="$headerMenu" :is-current="$isCurrent" :href="$href" />
        </nav>

        <div class="flex items-center gap-2" aria-label="{{ __('User account') }}">
            @if($searchEnabled)
                <a href="{{ route('search') }}" class="hidden size-10 place-items-center rounded-xl border border-line bg-surface text-ink-muted transition hover:border-primary hover:text-primary sm:grid" title="{{ __('Search') }}">
                    <span class="sr-only">{{ __('Search Cambo Hub') }}</span>
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </a>
            @endif
            <x-language-switcher />
            <x-ui.theme-toggle />
            @auth
                <div class="relative hidden xl:block">
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
                <a href="{{ route('login') }}" class="hidden h-10 items-center rounded-xl px-3 text-sm font-semibold whitespace-nowrap text-primary transition hover:bg-primary-soft xl:inline-flex">{{ __('User sign in') }}</a>
                @if($registrationOpen)
                    <x-ui.button :href="route('register')" size="sm" class="header-register-link hidden h-10 sm:inline-flex">{{ __('Create account') }}</x-ui.button>
                @endif
            @endauth
            <button type="button" data-disclosure aria-controls="mobile-menu" aria-expanded="false" class="grid size-10 cursor-pointer place-items-center rounded-xl border border-line bg-surface text-ink transition hover:border-primary xl:hidden">
                <span class="sr-only">{{ __('Main navigation') }}</span>
                <svg class="size-5 [[aria-expanded=true]>&]:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                <svg class="hidden size-5 [[aria-expanded=true]>&]:block" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <div id="mobile-menu" hidden class="max-h-[calc(100svh-6rem)] overflow-y-auto rounded-b-2xl border-t border-line xl:hidden">
        <nav class="mx-auto grid max-w-7xl gap-1 px-4 py-4 sm:grid-cols-2 sm:px-6" aria-label="{{ __('Main navigation') }}">
            <x-public-nav-links :items="$headerMenu" :is-current="$isCurrent" :href="$href" layout="mobile" />
            @if($searchEnabled)
                <a href="{{ route('search') }}" class="flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-medium text-ink hover:bg-surface-muted sm:hidden"><x-ui.icon name="search" :size="18" class="size-[18px] opacity-70" />{{ __('Search') }}</a>
            @endif
        </nav>
        <div class="mx-auto flex max-w-7xl flex-wrap gap-2 border-t border-line px-4 py-4 sm:px-6">
            @auth
                <x-ui.button :href="route('dashboard')" size="sm">{{ __('My dashboard') }}</x-ui.button>
                <x-ui.button :href="route('account.edit')" variant="secondary" size="sm">{{ __('Account settings') }}</x-ui.button>
                <form method="POST" action="{{ route('logout') }}">@csrf<x-ui.button variant="ghost" size="sm" class="text-danger!">{{ __('Sign out') }}</x-ui.button></form>
            @else
                @if($registrationOpen)<x-ui.button :href="route('register')" size="sm">{{ __('Create account') }}</x-ui.button>@endif
                <x-ui.button :href="route('login')" variant="secondary" size="sm">{{ __('User sign in') }}</x-ui.button>
            @endauth
        </div>
    </div>
    </div>
</header>

<main id="main-content" tabindex="-1" class="flex-1 overflow-x-clip focus:outline-none">@yield('content')</main>

<footer class="relative isolate mt-16 overflow-hidden border-t border-line bg-surface">
    <div class="pattern-khmer pointer-events-none absolute inset-0 -z-10 text-primary/[0.06]" aria-hidden="true"></div>
    {{-- Angkor Wat's five lotus-bud towers, drawn as a skyline along the top of the footer. --}}
    <svg class="pointer-events-none absolute inset-x-0 top-0 -z-10 mx-auto h-24 w-full max-w-4xl text-primary/[0.07] sm:h-32" viewBox="0 0 600 120" preserveAspectRatio="xMidYMax meet" fill="currentColor" aria-hidden="true">
        <path d="M20 120v-14h70v-10h40l6-26q5-18 14-26 5-18 10 0 9 8 14 26l6 26h22l7-38q6-24 16-34 6-22 12 0 10 10 16 34l7 38h18l8-56q7-30 18-42 6-26 13 0 11 12 18 42l8 56h18l7-38q6-24 16-34 6-22 12 0 10 10 16 34l7 38h22l6-26q5-18 14-26 5-18 10 0 9 8 14 26l6 26h40v10h70v14Z"/>
    </svg>
    <div class="mx-auto grid max-w-7xl gap-10 px-4 pt-28 pb-12 sm:px-6 sm:pt-36 lg:grid-cols-[1fr_2fr] lg:px-8">
        <div class="max-w-xs">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 rounded-xl">
                <img src="{{ $logo }}" width="36" height="36" alt="" class="size-9" loading="lazy">
                <span class="text-2xl font-extrabold tracking-tight text-brand-700 dark:text-white">{{ $nameMain }}<span class="text-cambo-red">{{ $nameAccent }}</span></span>
            </a>
            @if(filled($footer['about']))<p class="mt-3 text-sm leading-relaxed text-ink-muted">{{ $footer['about'] }}</p>@endif
            @if($footer['social_links'] !== [])
                <ul class="mt-5 flex flex-wrap gap-2" aria-label="{{ __('Follow us') }}">
                    @foreach($footer['social_links'] as $network => $url)
                        <li><a href="{{ $url }}" rel="noopener me" target="_blank" class="inline-flex h-9 items-center rounded-xl border border-line px-3 text-xs font-semibold text-ink-muted capitalize transition hover:border-primary hover:text-primary">{{ $network === 'tiktok' ? 'TikTok' : ($network === 'linkedin' ? 'LinkedIn' : ($network === 'youtube' ? 'YouTube' : ucfirst($network))) }}<span class="sr-only"> ({{ __('opens in a new tab') }})</span></a></li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="grid grid-cols-2 gap-8 sm:grid-cols-3 lg:grid-cols-4">
            @foreach($footer['menu'] as $column)
                <nav aria-label="{{ __($column['label']) }}">
                    <h2 class="text-sm font-semibold text-ink">{{ __($column['label']) }}</h2>
                    <ul class="mt-3 space-y-2">
                        @foreach($column['children'] as $link)
                            <li><a href="{{ $href($link['url']) }}" @if($link['new_tab']) target="_blank" rel="noopener" @endif class="text-sm text-ink-muted transition hover:text-primary">{{ __($link['label']) }}</a></li>
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
                        @if($registrationOpen)<li><a href="{{ route('register') }}" class="text-sm text-ink-muted transition hover:text-primary">{{ __('Create an account') }}</a></li>@endif
                    @endauth
                </ul>
            </nav>
        </div>
    </div>
    <p class="pointer-events-none mx-auto max-w-7xl px-4 text-center text-[17vw] leading-[0.8] font-extrabold tracking-tighter text-transparent select-none [-webkit-text-stroke:1px_var(--color-line-strong)] sm:px-6 lg:text-[11rem] lg:px-8" aria-hidden="true">{{ $siteName }}</p>
    <div class="border-t border-line">
        <div class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-5 text-xs text-ink-subtle sm:flex-row sm:justify-between sm:px-6 lg:px-8">
            <span>{{ $footer['copyright'] }}</span>
            <span class="flex items-center gap-1.5">{{ __('Made for Cambodia. Built around you.') }} <span class="inline-flex h-3 w-4.5 flex-col overflow-hidden rounded-[2px]" aria-hidden="true"><span class="h-1/4 bg-brand-700"></span><span class="h-1/2 bg-cambo-red"></span><span class="h-1/4 bg-brand-700"></span></span></span>
        </div>
    </div>
</footer>
</body>
</html>
