@extends('layouts.public')
@php($purpose = config('purpose.'.$page))
@section('title', __($purpose['label']))
@section('description', __($purpose['intro']))
@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    {{-- Heading, statement, why it matters and principles play out in the floating-island scroll story. --}}
    <x-purpose-story :page="$page" :purpose="$purpose" />

    <section id="our-purpose" class="grid gap-5 py-14 md:grid-cols-3" aria-label="{{ __('Our priorities') }}">
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
