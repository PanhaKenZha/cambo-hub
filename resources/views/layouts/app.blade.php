{{--
    Client account pages (sign in, registration, dashboard, settings).
    Child views fill @section('page'); set @section('auth-art', url) for the illustrated split layout
    and @section('width', 'wide') for dashboard style pages.
--}}
@extends('layouts.public')
@section('content')
    @hasSection('auth-art')
        <div class="auth-layout mx-auto grid max-w-6xl items-center gap-12 px-4 py-10 sm:px-6 sm:py-14 lg:grid-cols-[1fr_minmax(0,28rem)] lg:gap-20 lg:px-8">
            <aside class="auth-story hidden lg:block" aria-label="{{ __('Welcome to Cambo Hub') }}">
                <x-ui.eyebrow>{{ __('YOUR EVERYDAY CAMBODIA') }}</x-ui.eyebrow>
                <h2 class="mt-4 text-4xl leading-tight font-bold tracking-tight text-ink">{{ __('One account.') }}<br><span class="text-primary">{{ __('More possibilities.') }}</span></h2>
                <p class="mt-4 max-w-md text-[15px] leading-relaxed text-ink-muted">{{ __('Discover opportunities, connect with your community, and keep your activities together.') }}</p>
                <div class="skeleton mt-8 overflow-hidden rounded-3xl bg-primary-soft">
                    <img src="@yield('auth-art')" width="960" height="640" alt="@yield('auth-art-alt', __('Growth and new possibilities'))" data-placeholder class="aspect-[3/2] w-full object-cover">
                </div>
                <a href="{{ route('mission') }}" class="mt-6 inline-flex text-sm font-semibold text-primary hover:underline">{{ __('Discover our mission') }} <span aria-hidden="true" class="ml-1">→</span></a>
            </aside>
            <div class="animate-reveal">
                @include('layouts.partials.client-status')
                @yield('page')
            </div>
        </div>
    @else
        @auth
        <div class="account-layout mx-auto grid max-w-7xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[14rem_minmax(0,1fr)] lg:gap-8 lg:px-8 lg:py-12">
            <aside class="account-navigation lg:sticky lg:top-28 lg:self-start">
                <a href="{{ route('dashboard') }}" class="mb-6 hidden items-center gap-3 border-b border-line pb-6 lg:flex">
                    <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-primary text-lg font-bold text-on-primary" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="min-w-0"><strong class="block truncate text-sm">{{ auth()->user()->name }}</strong><span class="text-xs text-ink-muted">{{ __('My account') }}</span></span>
                </a>
                <nav class="flex gap-2 overflow-x-auto lg:flex-col" aria-label="{{ __('Account') }}">
                    @foreach(['dashboard' => ['label' => 'My dashboard', 'icon' => 'menu'], 'account.edit' => ['label' => 'Account settings', 'icon' => 'profile'], 'account.standing' => ['label' => 'Account standing', 'icon' => 'shield']] as $routeName => $item)
                        <a href="{{ route($routeName) }}" @if(request()->routeIs($routeName)) aria-current="page" @endif class="flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-ink-muted transition hover:bg-primary-soft hover:text-primary aria-[current=page]:bg-primary aria-[current=page]:text-on-primary">
                            <x-ui.icon :name="$item['icon']" :size="18" class="size-[18px] rounded bg-icon-tile p-0.5" />{{ __($item['label']) }}
                        </a>
                    @endforeach
                </nav>
                <div class="mt-6 hidden border-t border-line pt-5 lg:block">
                    <a href="{{ route('jobs.index') }}" class="block rounded-xl px-4 py-2 text-sm text-ink-muted hover:text-primary">{{ __('Jobs') }} <span class="float-right" aria-hidden="true">&nearr;</span></a>
                    <a href="{{ route('news.index') }}" class="block rounded-xl px-4 py-2 text-sm text-ink-muted hover:text-primary">{{ __('News') }} <span class="float-right" aria-hidden="true">&nearr;</span></a>
                </div>
            </aside>
            <div class="min-w-0">
                @include('layouts.partials.client-status')
                @yield('page')
            </div>
        </div>
        @else
        <div @class([
            'mx-auto px-4 py-10 sm:px-6 sm:py-14 lg:px-8',
            'max-w-6xl' => trim($__env->yieldContent('width')) === 'wide',
            'max-w-xl' => trim($__env->yieldContent('width')) !== 'wide',
        ])>
            @include('layouts.partials.client-status')
            @yield('page')
        </div>
        @endauth
    @endif
@endsection
