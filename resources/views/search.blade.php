@extends('layouts.public')
@section('title', $term !== '' ? __('Search results for “:term”', ['term' => $term]) : __('Search'))
@section('description', __('Search news, jobs, products, businesses, property, events, and community posts on Cambo Hub.'))
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
    <header class="max-w-3xl">
        <x-ui.eyebrow>{{ __('GLOBAL SEARCH') }}</x-ui.eyebrow>
        <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">{{ __('Search Cambo Hub') }}</h1>
        <form action="{{ route('search') }}" method="get" role="search" data-loading data-loading-target="search-loading" data-loading-hide="search-results"
            class="mt-6 flex items-center gap-2 rounded-2xl border border-line-strong bg-surface p-1.5 pl-4 shadow-card transition focus-within:border-brand-500 focus-within:ring-3 focus-within:ring-brand-500/20">
            <label for="search-term" class="sr-only">{{ __('Search Cambo Hub') }}</label>
            <svg class="size-5 shrink-0 text-ink-subtle" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
            <input id="search-term" type="search" name="q" value="{{ $term }}" maxlength="100" placeholder="{{ __($placeholder ?? 'Search') }}" autocomplete="off" enterkeyhint="search"
                @error('q') aria-invalid="true" aria-describedby="search-term-error" @enderror
                class="h-11 min-w-0 flex-1 bg-transparent text-sm text-ink placeholder:text-ink-subtle focus:outline-none">
            @if($type)<input type="hidden" name="type" value="{{ $type->value }}">@endif
            <x-ui.button class="shrink-0">{{ __('Search') }}</x-ui.button>
        </form>
        @error('q')<p id="search-term-error" class="mt-2 text-xs font-medium text-danger">{{ $message }}</p>@enderror
        @error('type')<p class="mt-2 text-xs font-medium text-danger">{{ $message }}</p>@enderror

        <nav class="scrollbar-none -mx-4 mt-5 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:flex-wrap sm:px-0" aria-label="{{ __('Filter by service') }}">
            <a href="{{ route('search', array_filter(['q' => $term])) }}" @if(! $type) aria-current="true" @endif class="shrink-0 rounded-full border border-line bg-surface px-4 py-1.5 text-xs font-semibold text-ink-muted transition hover:border-primary hover:text-primary aria-[current=true]:border-primary aria-[current=true]:bg-primary aria-[current=true]:text-on-primary">{{ __('All') }}</a>
            @foreach($types as $filter)
                <a href="{{ route('search', array_filter(['q' => $term, 'type' => $filter->value])) }}" @if($type === $filter) aria-current="true" @endif class="shrink-0 rounded-full border border-line bg-surface px-4 py-1.5 text-xs font-semibold text-ink-muted transition hover:border-primary hover:text-primary aria-[current=true]:border-primary aria-[current=true]:bg-primary aria-[current=true]:text-on-primary">{{ __($filter->label()) }}</a>
            @endforeach
        </nav>
    </header>

    <x-ui.skeleton id="search-loading" variant="list" :count="4" class="mt-10" hidden :label="__('Searching…')" />

    <div id="search-results" class="mt-10" aria-live="polite">
        @if($term === '' && ! $type)
            <x-ui.empty-state image="images/backgrounds/empty-search.webp" :title="__('What are you looking for?')">
                @foreach($suggestions as $suggestion)
                    <a href="{{ route('search', ['q' => $suggestion]) }}" class="rounded-full border border-line bg-surface-muted px-3.5 py-1.5 text-xs font-medium text-ink-muted transition hover:border-primary hover:text-primary">{{ $suggestion }}</a>
                @endforeach
            </x-ui.empty-state>
        @elseif($results->isEmpty())
            <x-ui.empty-state image="images/backgrounds/empty-search.webp" :title="__('No results found')" :description="__('Try a different keyword or browse another service.')">
                <x-ui.button :href="route('search')" variant="secondary" size="sm">{{ __('Clear search') }}</x-ui.button>
            </x-ui.empty-state>
        @else
            <p class="mb-6 text-sm text-ink-muted">{{ trans_choice(':count result|:count results', $results->flatten()->count()) }}</p>
            <div class="space-y-10">
                @foreach($results as $typeValue => $items)
                    @php($resultType = \App\Enums\CmsContentType::from($typeValue))
                    <section aria-labelledby="results-{{ $typeValue }}">
                        <h2 id="results-{{ $typeValue }}" class="mb-4 flex items-center gap-2.5 text-lg font-semibold">
                            <span class="grid size-9 place-items-center rounded-xl bg-icon-tile"><img src="{{ asset('images/icons/'.$resultType->icon().'-blue.svg') }}" width="18" height="18" alt=""></span>
                            {{ __($resultType->label()) }} <span class="text-sm font-normal text-ink-subtle">({{ $items->count() }})</span>
                        </h2>
                        <ul class="grid gap-3 md:grid-cols-2">
                            @foreach($items as $item)
                                <li>
                                    <x-ui.card as="article" padding="p-4" interactive class="flex h-full items-start gap-4">
                                        <img src="{{ $item->imageUrl('images/icons/'.$resultType->icon().'-blue.svg') }}" width="64" height="64" alt="" loading="lazy" class="size-16 shrink-0 rounded-xl bg-surface-muted object-cover">
                                        <div class="min-w-0">
                                            <h3 class="text-sm leading-snug font-semibold">@if($item->linkUrl())<a href="{{ $item->linkUrl() }}" class="stretched-link">{{ $item->title }}</a>@else{{ $item->title }}@endif</h3>
                                            @if($item->summary)<p class="mt-1 line-clamp-2 text-xs text-ink-muted">{{ $item->summary }}</p>@endif
                                            @if($item->badge)<x-ui.badge class="mt-2">{{ $item->badge }}</x-ui.badge>@endif
                                        </div>
                                    </x-ui.card>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
