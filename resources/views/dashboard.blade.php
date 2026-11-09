@extends('layouts.app')
@section('title', 'Dashboard')
@section('width', 'wide')
@php
    $user = auth()->user();
    $services = \App\Models\CmsContent::query()->where('content_type', \App\Enums\CmsContentType::ServiceCategory)->visible()->curated()->limit(8)->get();
    $recentSignIns = $user->loginHistories()->latest()->limit(5)->get();
@endphp
@section('page')
<div class="space-y-8">
    <x-ui.card as="section" class="overflow-hidden bg-gradient-to-br from-brand-700 to-brand-900 text-white" aria-labelledby="dashboard-title">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-white/15 text-xl font-bold ring-1 ring-white/25" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                <div>
                    <p class="text-[11px] font-semibold tracking-[0.16em] text-brand-200 uppercase">YOUR DASHBOARD</p>
                    <h1 id="dashboard-title" class="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">Welcome, {{ $user->name }}.</h1>
                    <p class="text-sm text-brand-100">You're signed in and ready to get started.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ui.button :href="route('account.edit')" variant="white" size="sm">Manage your account <span aria-hidden="true">→</span></x-ui.button>
                <form method="POST" action="{{ route('logout') }}">@csrf<x-ui.button size="sm" class="bg-white/10! text-white! ring-1 ring-white/25 hover:bg-white/20!">Sign out</x-ui.button></form>
            </div>
        </div>
    </x-ui.card>

    <div class="grid gap-8 lg:grid-cols-[2fr_1fr]">
        <section aria-labelledby="services-title">
            <h2 id="services-title" class="mb-4 text-lg font-semibold">{{ __('Explore our services') }}</h2>
            @if($services->isEmpty())
                <x-ui.empty-state icon="menu" :title="__('No results found')" :description="__('Try a different keyword or browse another service.')" />
            @else
                <ul class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach($services as $service)
                        <li>
                            <x-ui.card padding="p-4" interactive class="flex h-full flex-col items-center gap-2 text-center">
                                <span class="grid size-11 place-items-center rounded-xl bg-icon-tile"><img src="{{ $service->imageUrl('images/icons/'.$service->detail('icon', 'menu').'-blue.svg') }}" width="22" height="22" alt="" loading="lazy"></span>
                                @if($service->linkUrl())
                                    <a href="{{ $service->linkUrl() }}" class="stretched-link text-sm font-semibold">{{ $service->title }}</a>
                                @else
                                    <span class="text-sm font-semibold">{{ $service->title }}</span>
                                @endif
                            </x-ui.card>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <x-ui.card as="section" padding="p-5" aria-labelledby="profile-title" class="h-fit">
            <h2 id="profile-title" class="text-lg font-semibold">{{ __('Your profile') }}</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div><dt class="text-ink-subtle">{{ __('Full name') }}</dt><dd class="font-medium break-words">{{ $user->name }}</dd></div>
                <div><dt class="text-ink-subtle">{{ __('Email address') }}</dt><dd class="font-medium break-all">{{ $user->email }}</dd></div>
                <div><dt class="text-ink-subtle">{{ __('Member since') }}</dt><dd class="font-medium">{{ $user->created_at?->translatedFormat('j M Y') }}</dd></div>
            </dl>
        </x-ui.card>
    </div>

    <section aria-labelledby="activity-title">
        <h2 id="activity-title" class="mb-4 text-lg font-semibold">{{ __('Recent sign-in activity') }}</h2>
        @if($recentSignIns->isEmpty())
            <x-ui.empty-state icon="shield" :title="__('No sign-in activity yet')" :description="__('Your recent sign-ins will appear here so you can spot anything unusual.')" />
        @else
            <x-ui.table :label="__('Recent sign-in activity')">
                <x-slot:head><tr><th scope="col">{{ __('Date') }}</th><th scope="col">{{ __('IP address') }}</th><th scope="col">{{ __('Device') }}</th><th scope="col">{{ __('Status') }}</th></tr></x-slot:head>
                @foreach($recentSignIns as $signIn)
                    <tr>
                        <td data-label="{{ __('Date') }}" class="whitespace-nowrap text-ink">{{ $signIn->created_at?->translatedFormat('j M Y, H:i') }}</td>
                        <td data-label="{{ __('IP address') }}" class="font-mono text-xs">{{ $signIn->ip_address ?? '—' }}</td>
                        <td data-label="{{ __('Device') }}" class="max-w-xs truncate" title="{{ $signIn->user_agent }}">{{ \Illuminate\Support\Str::limit($signIn->user_agent ?? '—', 48) }}</td>
                        <td data-label="{{ __('Status') }}">
                            @if(! $signIn->successful)
                                <x-ui.badge variant="danger">{{ __('Failed') }}</x-ui.badge>
                            @elseif($signIn->is_suspicious)
                                <x-ui.badge variant="warning">{{ __('Unusual') }}</x-ui.badge>
                            @else
                                <x-ui.badge variant="success">{{ __('Successful') }}</x-ui.badge>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        @endif
    </section>
</div>
@endsection
