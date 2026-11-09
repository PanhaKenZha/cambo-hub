{{--
    Client account pages (sign in, registration, dashboard, settings).
    Child views fill @section('page'); set @section('auth-art', url) for the illustrated split layout
    and @section('width', 'wide') for dashboard style pages.
--}}
@extends('layouts.public')
@section('content')
    @hasSection('auth-art')
        <div class="mx-auto grid max-w-6xl items-center gap-12 px-4 py-10 sm:px-6 sm:py-14 lg:grid-cols-[1fr_minmax(0,28rem)] lg:gap-20 lg:px-8">
            <aside class="hidden lg:block" aria-label="{{ __('Welcome to Cambo Hub') }}">
                <x-ui.eyebrow>{{ __('YOUR EVERYDAY CAMBODIA') }}</x-ui.eyebrow>
                <h2 class="mt-4 text-4xl leading-tight font-bold tracking-tight text-ink">{{ __('One account.') }}<br><span class="text-primary">{{ __('More possibilities.') }}</span></h2>
                <p class="mt-4 max-w-md text-[15px] leading-relaxed text-ink-muted">{{ __('Discover opportunities, connect with your community, and keep your activities together.') }}</p>
                <div class="skeleton mt-8 overflow-hidden rounded-3xl bg-primary-soft">
                    <img src="@yield('auth-art')" width="508" height="337" alt="{{ __('Illustration of a connected Cambo Hub experience') }}" data-placeholder class="aspect-[3/2] w-full object-cover">
                </div>
                <a href="{{ route('mission') }}" class="mt-6 inline-flex text-sm font-semibold text-primary hover:underline">{{ __('Discover our mission') }} <span aria-hidden="true" class="ml-1">→</span></a>
            </aside>
            <div class="animate-reveal">
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
    @endif
@endsection
