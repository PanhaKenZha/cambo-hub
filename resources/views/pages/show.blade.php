@extends('layouts.public')
@section('title', $title)
@section('description', $metaDescription ?? '')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
    @if($isPreview)
        <x-ui.alert variant="warning" class="mb-6">
            Preview{{ isset($page) ? ' · '.$page->stateLabel() : '' }} — visitors {{ isset($page) && $page->isLive() ? 'can' : 'cannot yet' }} see this page.
        </x-ui.alert>
    @endif
    <x-ui.card as="article" class="animate-reveal">
        <h1 class="text-3xl leading-tight font-bold tracking-tight sm:text-4xl">{{ $title }}</h1>
        <div class="cms-prose mt-6">{!! $html !!}</div>
    </x-ui.card>
</div>
@endsection
