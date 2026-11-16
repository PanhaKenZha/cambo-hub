@extends('layouts.public')
@inject('platformSettings', 'App\Services\PlatformSettings')
@php
    // Each page opens with one wide story followed by a grid of twelve.
    $lead = $articles->first();
    $rest = $articles->getCollection()->skip(1);
    $publishedAt = fn ($article) => $article->published_at ?? $article->created_at;
@endphp
@section('title', $selectedCategory ? $selectedCategory->translate('name', $locale).' · '.__('News') : __('News'))
@section('description', $selectedCategory?->translate('description', $locale) ?: __('The latest headlines and stories from across Cambodia.'))
@section('content')
<section class="news-masthead public-page-hero relative isolate overflow-hidden border-b border-line bg-surface">
    <div class="pattern-khmer pointer-events-none absolute inset-0 -z-10 text-primary/[0.05]" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -top-32 right-0 -z-10 size-96 rounded-full bg-cambo-red/10 blur-3xl animate-aurora" aria-hidden="true"></div>
    <div class="mx-auto max-w-7xl px-4 pt-10 sm:px-6 sm:pt-14 lg:px-8">
        <nav aria-label="{{ __('Breadcrumb') }}" class="text-xs text-ink-subtle">
            <a href="{{ route('home') }}" class="hover:text-primary">{{ __('Home') }}</a> <span aria-hidden="true" class="mx-1">/</span>
            @if($selectedCategory)
                <a href="{{ route('news.index') }}" class="hover:text-primary">{{ __('News') }}</a> <span aria-hidden="true" class="mx-1">/</span>
                <span aria-current="page" class="text-ink-muted">{{ $selectedCategory->translate('name', $locale) }}</span>
            @else
                <span aria-current="page" class="text-ink-muted">{{ __('News') }}</span>
            @endif
        </nav>
        <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl leading-tight font-bold tracking-tight sm:text-4xl lg:text-5xl">{{ $selectedCategory ? $selectedCategory->translate('name', $locale) : __('Latest news') }}</h1>
                <p class="mt-2 max-w-xl text-base text-ink-muted">{{ $selectedCategory?->translate('description', $locale) ?: __('The latest headlines and stories from across Cambodia.') }}</p>
            </div>
            <time class="text-sm text-ink-subtle" datetime="{{ now()->toDateString() }}">{{ now()->translatedFormat('l, j F Y') }}</time>
        </div>

        @if($categories->isNotEmpty())
            <nav class="news-topics scrollbar-none -mx-4 mt-8 flex gap-2 overflow-x-auto px-4 pb-5 sm:mx-0 sm:px-0" aria-label="{{ __('News categories') }}">
                <a href="{{ route('news.index') }}" @class(['shrink-0 border-b-2 px-3 pb-3 text-sm font-semibold whitespace-nowrap transition', 'border-primary text-primary' => ! $selectedCategory, 'border-transparent text-ink-muted hover:text-ink' => $selectedCategory]) @if(! $selectedCategory) aria-current="page" @endif>{{ __('All') }}</a>
                @foreach($categories as $category)
                    @php($isActive = $selectedCategory?->is($category))
                    <a href="{{ route('news.index', ['category' => $category->slug]) }}" @class(['shrink-0 border-b-2 px-3 pb-3 text-sm font-semibold whitespace-nowrap transition', 'border-primary text-primary' => $isActive, 'border-transparent text-ink-muted hover:text-ink' => ! $isActive]) @if($isActive) aria-current="page" @endif>
                        {{ $category->translate('name', $locale) }} <span class="ml-0.5 text-xs font-normal text-ink-subtle tabular-nums">{{ $category->articles_count }}</span>
                    </a>
                @endforeach
            </nav>
        @endif
    </div>
</section>

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:py-12 lg:px-8">
    @if($lead === null)
        <x-ui.empty-state icon="news" :title="__('No stories here yet')" :description="__('Check back soon for the latest news.')">
            @if($selectedCategory)<x-ui.button :href="route('news.index')" variant="secondary" size="sm">{{ __('See all news') }}</x-ui.button>@endif
        </x-ui.empty-state>
    @else
        <x-ui.card as="article" padding="p-0" interactive class="news-feature animate-reveal grid overflow-hidden md:grid-cols-[1.35fr_1fr]">
            <div class="skeleton aspect-video md:aspect-auto md:min-h-80">
                <img src="{{ $lead->coverUrl() ?? asset('images/backgrounds/news.webp') }}" width="800" height="450" alt="" fetchpriority="high" data-placeholder class="size-full object-cover">
            </div>
            <div class="flex flex-col justify-center p-6 sm:p-8">
                <div class="flex flex-wrap items-center gap-2 text-xs text-ink-subtle">
                    @if($lead->is_featured)<x-ui.badge variant="solid">{{ __('Top story') }}</x-ui.badge>@endif
                    @if($lead->category)<x-ui.badge>{{ $lead->category->translate('name', $locale) }}</x-ui.badge>@endif
                    <time datetime="{{ $publishedAt($lead)->toIso8601String() }}">{{ $publishedAt($lead)->translatedFormat('j M Y') }}</time>
                </div>
                <h2 class="mt-4 text-2xl leading-snug font-bold tracking-tight sm:text-3xl"><a href="{{ $lead->publicUrl() }}" class="stretched-link hover:text-primary">{{ $lead->translate('title', $locale) }}</a></h2>
                @if(filled($lead->translate('excerpt', $locale)))<p class="mt-3 line-clamp-3 text-base text-ink-muted">{{ $lead->translate('excerpt', $locale) }}</p>@endif
                @if($lead->author)
                    <p class="mt-6 flex items-center gap-2 text-sm text-ink-muted">
                        @if($lead->author->avatarUrl())<img src="{{ $lead->author->avatarUrl() }}" width="28" height="28" alt="" class="size-7 rounded-full object-cover">@endif
                        <span>{{ __('By :name', ['name' => $lead->author->name]) }}</span>
                    </p>
                @endif
            </div>
        </x-ui.card>

        <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_20rem]">
            <div class="min-w-0">
                @if($rest->isNotEmpty())
                    <ul class="grid gap-5 sm:grid-cols-2">
                        @foreach($rest as $article)
                            <li class="animate-reveal" style="animation-delay: {{ min($loop->index, 8) * 40 }}ms">
                                <x-ui.card as="article" padding="p-0" interactive class="flex h-full flex-col overflow-hidden">
                                    <div class="skeleton aspect-[16/9]">
                                        <img src="{{ $article->coverUrl() ?? asset('images/backgrounds/news.webp') }}" width="480" height="270" alt="" loading="lazy" data-placeholder class="size-full object-cover">
                                    </div>
                                    <div class="flex flex-1 flex-col p-5">
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-ink-subtle">
                                            @if($article->category)<x-ui.badge>{{ $article->category->translate('name', $locale) }}</x-ui.badge>@endif
                                            <time datetime="{{ $publishedAt($article)->toIso8601String() }}">{{ $publishedAt($article)->diffForHumans() }}</time>
                                        </div>
                                        <h3 class="mt-3 line-clamp-2 text-base leading-snug font-semibold"><a href="{{ $article->publicUrl() }}" class="stretched-link hover:text-primary">{{ $article->translate('title', $locale) }}</a></h3>
                                        @if(filled($article->translate('excerpt', $locale)))<p class="mt-2 line-clamp-2 text-sm text-ink-muted">{{ $article->translate('excerpt', $locale) }}</p>@endif
                                        @if($article->author)<p class="mt-auto pt-4 text-xs text-ink-subtle">{{ __('By :name', ['name' => $article->author->name]) }}</p>@endif
                                    </div>
                                </x-ui.card>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <x-ui.pagination :paginator="$articles" class="mt-10" />
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
                @if($mostRead->isNotEmpty())
                    <section class="rounded-2xl border border-line bg-surface p-5" aria-labelledby="most-read-heading">
                        <h2 id="most-read-heading" class="flex items-center gap-2 text-sm font-bold tracking-wide text-ink uppercase"><span class="size-2 rounded-full bg-cambo-red" aria-hidden="true"></span>{{ __('Most read') }}</h2>
                        <ol class="mt-4 divide-y divide-line">
                            @foreach($mostRead as $article)
                                <li class="relative flex gap-4 py-3.5 first:pt-0 last:pb-0">
                                    <span class="text-2xl leading-none font-extrabold text-line-strong tabular-nums" aria-hidden="true">{{ $loop->iteration }}</span>
                                    <div class="min-w-0">
                                        <a href="{{ $article->publicUrl() }}" class="stretched-link line-clamp-2 text-sm leading-snug font-semibold text-ink hover:text-primary">{{ $article->translate('title', $locale) }}</a>
                                        <p class="mt-1 text-xs text-ink-subtle">{{ trans_choice(':count view|:count views', $article->views_count, ['count' => number_format($article->views_count)]) }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                @endif
                <section class="overflow-hidden rounded-2xl bg-gradient-to-br from-brand-700 to-brand-900 p-6 text-white">
                    <h2 class="text-lg font-bold">{{ __('Stay in the loop') }}</h2>
                    <p class="mt-1 text-sm text-white/80">{{ __('Create a free account to comment on stories and follow what matters to you.') }}</p>
                    @guest
                        @if($platformSettings->get('registration.enabled', true))
                            <x-ui.button :href="route('register')" variant="white" size="sm" class="mt-4">{{ __('Create account') }}</x-ui.button>
                        @else
                            <x-ui.button :href="route('login')" variant="white" size="sm" class="mt-4">{{ __('User sign in') }}</x-ui.button>
                        @endif
                    @else
                        <x-ui.button :href="route('dashboard')" variant="white" size="sm" class="mt-4">{{ __('My dashboard') }}</x-ui.button>
                    @endguest
                </section>
            </aside>
        </div>
    @endif
</div>
@endsection
