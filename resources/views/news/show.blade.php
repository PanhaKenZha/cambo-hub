@extends('layouts.public')
@php
    $title = $article->translate('title', $locale);
    $metaTitle = $article->translate('meta_title', $locale) ?: $title;
    $metaDescription = $article->translate('meta_description', $locale) ?: $article->translate('excerpt', $locale);
@endphp
@section('title', $metaTitle)
@section('description', $metaDescription)
@section('content')
<article class="mx-auto max-w-3xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
    @if($isPreview)
        <x-ui.alert variant="warning" class="mb-6">
            Preview · {{ $article->stateLabel() }} — readers {{ $article->isLive() ? 'can' : 'cannot yet' }} see this article.
        </x-ui.alert>
    @endif

    <header class="animate-reveal">
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
        </div>
    </header>

    @if($article->coverUrl())
        <img src="{{ $article->coverUrl() }}" alt="" class="mt-8 aspect-video w-full rounded-2xl object-cover ring-1 ring-line">
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
@endsection
