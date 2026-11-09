@extends('layouts.public')
@section('title', __('Under maintenance'))
@section('content')
<div class="mx-auto max-w-xl px-4 py-16 sm:px-6 sm:py-24">
    <x-ui.empty-state image="images/backgrounds/maintenance.webp" :title="__('We\'re doing some maintenance')" :description="$message">
        <p class="text-xs font-semibold tracking-[0.16em] text-primary uppercase">{{ __('BACK SOON') }}</p>
    </x-ui.empty-state>
</div>
@endsection
