@extends('layouts.admin')
@section('title', 'News articles')
@section('content')
@php
    use App\Enums\PermissionAction;
    use App\Enums\PermissionArea;

    $canManage = auth()->user()->can(PermissionArea::News->permission(PermissionAction::Manage));
@endphp
@include('admin.news._nav')

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-ink">Articles</h1>
        <p class="mt-1 text-sm text-ink-muted">Write, schedule, feature and publish news. Published articles appear on the homepage and in search automatically.</p>
    </div>
    @can(PermissionArea::News->permission(PermissionAction::Create))
        <x-admin.button :href="route('admin.news.articles.create')">
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
            New article
        </x-admin.button>
    @endcan
</div>

{{-- Status tabs --}}
<nav class="mt-6 flex gap-1 overflow-x-auto" aria-label="Filter by status">
    @foreach($tabs as $key => $label)
        @php($isActive = $key === $activeTab)
        <a href="{{ route('admin.news.articles.index', array_merge(request()->except(['tab', 'page']), ['tab' => $key])) }}" @if($isActive) aria-current="page" @endif
           @class([
               'flex shrink-0 items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium whitespace-nowrap',
               'bg-primary-soft text-primary ring-1 ring-primary/20' => $isActive,
               'text-ink-muted hover:bg-surface-muted hover:text-ink' => ! $isActive,
           ])>
            {{ $label }} <span class="text-xs tabular-nums opacity-70">{{ $tabCounts[$key] }}</span>
        </a>
    @endforeach
</nav>

<form method="GET" action="{{ route('admin.news.articles.index') }}" role="search" aria-label="Filter articles" data-loading
      class="mt-4 grid gap-3 rounded-2xl bg-surface p-4 shadow-card ring-1 ring-line sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_auto] lg:items-end">
    <input type="hidden" name="tab" value="{{ $activeTab }}">
    <x-admin.input name="search" label="Search" :value="$filters['search'] ?? ''" placeholder="Headline or address" />
    <x-admin.select name="category" label="Category">
        <option value="">All categories</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected((int) ($filters['category'] ?? 0) === $category->id)>{{ $category->translate('name', 'en') }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.select name="author" label="Author">
        <option value="">All authors</option>
        @foreach($authors as $author)
            <option value="{{ $author->id }}" @selected((int) ($filters['author'] ?? 0) === $author->id)>{{ $author->name }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.button variant="secondary">Filter</x-admin.button>
</form>

@if($articles->isEmpty())
    <x-ui.empty-state class="mt-4" title="No articles here." />
@else
    <x-ui.table label="News articles" class="mt-4">
        <x-slot:head>
            <tr>
                <th scope="col">Article</th>
                <th scope="col">Category</th>
                <th scope="col">Author</th>
                <th scope="col">Status</th>
                <th scope="col" class="text-right">Views</th>
                <th scope="col"><span class="sr-only">Actions</span></th>
            </tr>
        </x-slot:head>
        @foreach($articles as $article)
            <tr class="hover:bg-surface-muted/60">
                <td data-label="Article">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="hidden h-12 w-20 shrink-0 overflow-hidden rounded-lg bg-surface-muted ring-1 ring-line sm:block">
                            @if($article->coverUrl())<img src="{{ $article->coverUrl() }}" alt="" class="size-full object-cover">@endif
                        </div>
                        <div class="min-w-0">
                            <a href="{{ route('admin.news.articles.edit', $article) }}" class="line-clamp-2 rounded font-medium text-ink hover:text-primary">
                                @if($article->is_featured)<span class="text-warning" title="Featured">★</span>@endif
                                {{ $article->translate('title', 'en') }}
                            </a>
                            <p class="font-mono text-xs break-all text-ink-subtle">/news/{{ $article->slug }}</p>
                        </div>
                    </div>
                </td>
                <td data-label="Category">{{ $article->category?->translate('name', 'en') ?? '—' }}</td>
                <td data-label="Author" class="whitespace-nowrap">{{ $article->author?->name ?? '—' }}</td>
                <td data-label="Status">
                    <div>
                        <x-admin.badge :color="$article->stateColor()">{{ $article->stateLabel() }}</x-admin.badge>
                        @if($article->published_at && $article->status === \App\Enums\ArticleStatus::Published)
                            <p class="mt-1 text-xs whitespace-nowrap text-ink-subtle">{{ $article->published_at->format('j M Y, H:i') }}</p>
                        @endif
                    </div>
                </td>
                <td data-label="Views" class="text-right tabular-nums">{{ number_format($article->views_count) }}</td>
                <td class="text-right whitespace-nowrap">
                    @if($canManage)
                        <form method="POST" action="{{ route('admin.news.articles.feature', $article) }}" class="inline">
                            @csrf
                            <button type="submit" class="cursor-pointer rounded text-sm font-semibold text-ink-muted hover:text-ink">{{ $article->is_featured ? 'Unfeature' : 'Feature' }}</button>
                        </form>
                    @endif
                    <a href="{{ route('admin.news.articles.preview', $article) }}" target="_blank" class="ml-3 rounded text-sm font-semibold text-ink-muted hover:text-ink">Preview</a>
                    @can(PermissionArea::News->permission(PermissionAction::Edit))
                        <a href="{{ route('admin.news.articles.edit', $article) }}" class="ml-3 rounded text-sm font-semibold text-primary hover:text-primary-hover">Edit</a>
                    @endcan
                </td>
            </tr>
        @endforeach
    </x-ui.table>
    @if($articles->hasPages())
        <div class="mt-4">{{ $articles->links() }}</div>
    @endif
@endif
@endsection
