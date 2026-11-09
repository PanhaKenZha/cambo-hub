@extends('layouts.public')
@inject('platformSettings', 'App\Services\PlatformSettings')
@section('title', __('Home'))
@section('description', __('Discover Cambo Hub, a connected platform for Cambodian opportunities, businesses, learning, and community.'))
@section('content')
@php($registrationOpen = (bool) $platformSettings->get('registration.enabled', true))
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    @forelse($sections as $section)
        @include('home.sections.'.$section['type'], ['section' => $section, 'items' => $section['items'], 'search' => $search, 'registrationOpen' => $registrationOpen])
    @empty
        <div class="py-16">
            <x-ui.empty-state image="images/content/empty-state.webp" :title="__('Fresh content is on its way')" :description="__('Check back soon for the latest news, jobs, products, and events.')" />
        </div>
    @endforelse
</div>
@endsection
