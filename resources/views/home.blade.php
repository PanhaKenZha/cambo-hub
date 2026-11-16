@extends('layouts.public')
@inject('platformSettings', 'App\Services\PlatformSettings')
@section('title', __('Home'))
@section('description', __('Discover Cambo Hub, a connected platform for Cambodian opportunities, businesses, learning, and community.'))
@section('content')
@php
    $registrationOpen = (bool) $platformSettings->get('registration.enabled', true);
    // Highlights sit under the hero, or open the page when the hero is switched off.
    $hasHero = $sections->contains('type', 'hero');
@endphp
<div class="homepage mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    @unless($hasHero)
        <x-home-highlights :stats="$stats" />
    @endunless
    @forelse($sections as $section)
        @if($section['type'] === 'hero')
            @include('home.sections.hero', ['section' => $section, 'search' => $search, 'registrationOpen' => $registrationOpen, 'stats' => $stats])
            <x-home-people-ring />
            <x-home-growth-scene />
            <x-home-highlights :stats="$stats" />
        @else
            <div id="chapter-{{ $section['type'] }}" tabindex="-1" class="home-content-section home-content-{{ $section['type'] }}" data-chapter="{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}" data-reveal data-reveal-stagger>
                @include('home.sections.'.$section['type'], ['section' => $section, 'items' => $section['items'], 'search' => $search, 'registrationOpen' => $registrationOpen])
            </div>
        @endif
    @empty
        <div class="py-16">
            <x-ui.empty-state image="images/content/empty-state.webp" :title="__('Fresh content is on its way')" :description="__('Check back soon for the latest news, jobs, products, and events.')" />
        </div>
    @endforelse
    @if($hasHero)
        <x-home-growth-gallery />
    @endif
    @if($sections->isNotEmpty())
        <nav class="journey-navigation" aria-label="{{ __('Explore our services') }}">
            <span class="journey-label" aria-hidden="true">{{ __('Cambodia, connected.') }}</span>
            <ol class="scrollbar-none flex min-w-0 flex-1 gap-1 overflow-x-auto p-1">
                @foreach($sections as $chapter)
                    <li class="shrink-0">
                        <a href="#chapter-{{ $chapter['type'] }}" class="journey-link">
                            <span class="journey-number" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            {{ $chapter['type'] === 'hero' ? __('Home') : __($chapter['title']) }}
                        </a>
                    </li>
                @endforeach
            </ol>
        </nav>
    @endif
</div>
@endsection
