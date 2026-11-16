@extends('layouts.public')
@php
    $title = $article->translate('title', $locale);
    $metaTitle = $article->translate('meta_title', $locale) ?: $title;
    $metaDescription = $article->translate('meta_description', $locale) ?: $article->translate('excerpt', $locale);
    // About 200 words a minute; Khmer has no spaces between words, so long texts are also estimated by characters.
    $plainBody = strip_tags($article->bodyHtml($locale));
    $readingMinutes = max(1, (int) ceil(str_word_count($plainBody) / 200), (int) ceil(mb_strlen($plainBody) / 1200));
    $relatedArticles ??= collect();
@endphp
@section('title', $metaTitle)
@section('description', $metaDescription)
@section('content')
<article class="news-reader mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
    @if($isPreview)
        <x-ui.alert variant="warning" class="mb-6">
            Preview · {{ $article->stateLabel() }} — readers {{ $article->isLive() ? 'can' : 'cannot yet' }} see this article.
        </x-ui.alert>
    @endif

    <header class="animate-reveal">
        @unless($isPreview)
            <nav aria-label="{{ __('Breadcrumb') }}" class="mb-5 text-xs text-ink-subtle">
                <a href="{{ route('home') }}" class="hover:text-primary">{{ __('Home') }}</a> <span aria-hidden="true" class="mx-1">/</span>
                <a href="{{ route('news.index') }}" class="hover:text-primary">{{ __('News') }}</a>
                @if($article->category)
                    <span aria-hidden="true" class="mx-1">/</span> <a href="{{ route('news.index', ['category' => $article->category->slug]) }}" class="hover:text-primary">{{ $article->category->translate('name', $locale) }}</a>
                @endif
            </nav>
        @endunless
        @if($article->category)
            <x-ui.badge>{{ $article->category->translate('name', $locale) }}</x-ui.badge>
        @endif
        <h1 class="mt-3 text-3xl leading-tight font-bold tracking-tight text-ink sm:text-4xl">{{ $title }}</h1>
        @if($article->translate('excerpt', $locale))
            <p class="mt-4 text-lg text-ink-muted">{{ $article->translate('excerpt', $locale) }}</p>
        @endif
        <div class="mt-5 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-ink-subtle">
            @if($article->author)
                <span class="flex items-center gap-2">
                    @if($article->author->avatarUrl())<img src="{{ $article->author->avatarUrl() }}" alt="" class="size-7 rounded-full object-cover">@endif
                    <span class="font-medium text-ink">{{ $article->author->name }}</span>
                </span>
                <span aria-hidden="true">·</span>
            @endif
            @if($article->published_at)<time datetime="{{ $article->published_at->toIso8601String() }}">{{ $article->published_at->translatedFormat('j F Y') }}</time>@endif
            <span aria-hidden="true">·</span>
            <span>{{ __(':count min read', ['count' => $readingMinutes]) }}</span>
            @unless($isPreview)
                <span aria-hidden="true">·</span>
                <span>{{ trans_choice(':count view|:count views', $article->views_count, ['count' => number_format($article->views_count)]) }}</span>
            @endunless
        </div>
    </header>

    @if($article->coverUrl())
        <img src="{{ $article->coverUrl() }}" alt="" class="mt-8 aspect-video w-full rounded-2xl object-cover shadow-card ring-1 ring-line">
    @endif

    <div class="cms-prose mt-8">{!! $article->bodyHtml($locale) !!}</div>

    @unless($isPreview)
        <x-report-content type="news" :id="$article->id" class="mt-8" />
    @endunless

    @if($article->allow_comments && ! $isPreview)
        <section class="mt-12 border-t border-line pt-8" aria-labelledby="comments-heading">
            <h2 id="comments-heading" class="text-xl font-bold text-ink">{{ trans_choice(':count comment|:count comments', $comments->count()) }}</h2>

            @if(session('status'))
                <x-ui.alert variant="success" class="mt-4">{{ session('status') }}</x-ui.alert>
            @endif

            @auth
                <form method="POST" action="{{ route('news.comments.store', $article->slug) }}" class="mt-6 space-y-3" data-loading>
                    @csrf
                    <x-ui.textarea name="body" :label="__('Add a comment')" rows="3" required />
                    <x-ui.button :loading-text="__('Posting…')">{{ __('Post comment') }}</x-ui.button>
                </form>
            @else
                <p class="mt-4 text-sm text-ink-muted"><a href="{{ route('login') }}" class="font-semibold text-primary hover:underline">{{ __('Sign in') }}</a> {{ __('to join the conversation.') }}</p>
            @endauth

            <ul class="mt-8 space-y-5">
                @foreach($comments as $comment)
                    <li class="rounded-xl bg-surface-muted p-4">
                        <p class="text-sm font-semibold text-ink">{{ $comment->user?->name ?? __('Reader') }} <span class="font-normal text-ink-subtle">· {{ $comment->created_at->diffForHumans() }}</span></p>
                        <p class="mt-1 text-sm whitespace-pre-line text-ink-muted">{{ $comment->body }}</p>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</article>

@if($relatedArticles->isNotEmpty())
    <section class="border-t border-line bg-surface" aria-labelledby="related-heading">
        <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-end justify-between gap-4">
                <h2 id="related-heading" class="text-2xl font-bold tracking-tight">{{ __('Keep reading') }}</h2>
                <a href="{{ route('news.index', array_filter(['category' => $article->category?->slug])) }}" class="group inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold text-primary">{{ __('More news') }} <span aria-hidden="true" class="transition group-hover:translate-x-0.5">→</span></a>
            </div>
            <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($relatedArticles as $related)
                    @php($relatedDate = $related->published_at ?? $related->created_at)
                    <li>
                        <x-ui.card as="article" padding="p-0" interactive class="flex h-full flex-col overflow-hidden">
                            <div class="skeleton aspect-[16/9]">
                                <img src="{{ $related->coverUrl() ?? asset('images/backgrounds/news.webp') }}" width="480" height="270" alt="" loading="lazy" data-placeholder class="size-full object-cover">
                            </div>
                            <div class="flex flex-1 flex-col p-5">
                                <div class="flex flex-wrap items-center gap-2 text-xs text-ink-subtle">
                                    @if($related->category)<x-ui.badge>{{ $related->category->translate('name', $locale) }}</x-ui.badge>@endif
                                    <time datetime="{{ $relatedDate->toIso8601String() }}">{{ $relatedDate->translatedFormat('j M Y') }}</time>
                                </div>
                                <h3 class="mt-3 line-clamp-2 text-base leading-snug font-semibold"><a href="{{ $related->publicUrl() }}" class="stretched-link hover:text-primary">{{ $related->translate('title', $locale) }}</a></h3>
                            </div>
                        </x-ui.card>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
@endsection
