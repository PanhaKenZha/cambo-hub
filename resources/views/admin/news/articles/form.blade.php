@extends('layouts.admin')
@php
    use App\Enums\ArticleStatus;
    use App\Enums\PermissionAction;
    use App\Enums\PermissionArea;

    $isEditing = $article->exists;
    $canPublish = auth()->user()->can(PermissionArea::News->permission(PermissionAction::Manage));
    $status = old('status', $article->status === ArticleStatus::Published ? 'published' : 'draft');
    $canSubmit = ! $canPublish && in_array($article->status, [ArticleStatus::Draft, ArticleStatus::Rejected], true);
@endphp
@section('title', $isEditing ? 'Edit article' : 'New article')
@section('content')
@include('admin.news._nav')

<a href="{{ route('admin.news.articles.index') }}" class="rounded text-sm font-medium text-ink-muted hover:text-primary">&larr; Articles</a>
<div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex min-w-0 flex-wrap items-center gap-3">
        <h1 class="truncate text-2xl font-bold tracking-tight text-ink">{{ $isEditing ? $article->translate('title', 'en') : 'New article' }}</h1>
        @if($isEditing)<x-admin.badge :color="$article->stateColor()">{{ $article->stateLabel() }}</x-admin.badge>@endif
        @if($article->is_featured)<x-admin.badge color="amber">★ Featured</x-admin.badge>@endif
    </div>
    @if($isEditing)
        <div class="flex shrink-0 gap-2">
            <x-admin.button variant="secondary" :href="route('admin.news.articles.preview', $article)" target="_blank">Preview EN</x-admin.button>
            <x-admin.button variant="secondary" :href="route('admin.news.articles.preview', [$article, 'lang' => 'km'])" target="_blank">Preview KM</x-admin.button>
        </div>
    @endif
</div>

@if($latestReview && $article->status === ArticleStatus::InReview)
    <x-ui.alert variant="warning" class="mt-4">
        Waiting for review in the <a href="{{ route('admin.approvals.show', $latestReview) }}" class="font-semibold underline">Approval center</a>. An editor will publish it or send it back.
    </x-ui.alert>
@elseif($latestReview && $article->status === ArticleStatus::Rejected)
    <x-ui.alert variant="error" class="mt-4">
        Returned by {{ $latestReview->reviewer?->name ?? 'an editor' }}: {{ $latestReview->rejection_reason }}. Update the article and send it again.
    </x-ui.alert>
@endif

<x-ui.error-summary class="mt-4" />

<form method="POST" action="{{ $isEditing ? route('admin.news.articles.update', $article) : route('admin.news.articles.store') }}" enctype="multipart/form-data"
      id="article-form" class="mt-6 grid gap-6 xl:grid-cols-[1fr_21rem]" data-loading>
    @csrf
    @if($isEditing) @method('PUT') @endif

    <div class="min-w-0 space-y-6">
        <x-admin.card title="Story">
            <div class="space-y-5">
                <x-admin.translatable-field name="title" label="Headline" :value="$article->title" maxlength="200" required />
                <x-admin.translatable-field name="excerpt" label="Summary" type="textarea" rows="2" :value="$article->excerpt" maxlength="300"
                    hint="One or two sentences shown on the homepage, in search and in link previews." />
                <x-admin.translatable-field name="body" label="Article text" type="textarea" rows="18" :value="$article->body" required
                    hint="Formatting with Markdown: ## Heading, **bold**, *italic*, - list item, > quote, [link text](https://example.com). HTML is not allowed." />
            </div>
        </x-admin.card>

        <x-admin.card title="Search engines (SEO)" description="Optional. The headline and summary are used when empty.">
            <div class="space-y-5">
                <x-admin.translatable-field name="meta_title" label="SEO title" :value="$article->meta_title" maxlength="70" />
                <x-admin.translatable-field name="meta_description" label="SEO description" type="textarea" rows="2" :value="$article->meta_description" maxlength="160" />
            </div>
        </x-admin.card>
    </div>

    <div class="space-y-6">
        <x-admin.card title="Publishing">
            @if($canPublish)
                <div class="space-y-4">
                    <fieldset>
                        <legend class="text-sm font-medium text-ink">Status</legend>
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            @foreach(['draft' => 'Draft', 'published' => 'Published'] as $value => $label)
                                <label class="flex cursor-pointer items-center justify-center rounded-lg px-3 py-2 text-sm font-medium text-ink-muted ring-1 ring-line-strong has-checked:bg-primary-soft has-checked:text-primary has-checked:ring-primary">
                                    <input type="radio" name="status" value="{{ $value }}" class="sr-only" @checked($status === $value)> {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <x-admin.input name="published_at" type="datetime-local" label="Publish on" :value="$article->published_at?->format('Y-m-d\TH:i')"
                        hint="Leave empty to publish now. A future date schedules the article." />
                    <label class="flex items-center gap-2.5 text-sm text-ink">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1" class="size-4 rounded accent-primary" @checked(old('is_featured', $article->is_featured))>
                        ★ Feature on the homepage
                    </label>
                </div>
            @else
                <p class="text-sm text-ink-muted">
                    @switch($article->status)
                        @case(ArticleStatus::InReview) This article is waiting for an editor. You can still fix mistakes. @break
                        @case(ArticleStatus::Published) This article is live. Your changes appear as soon as you save. @break
                        @default Save your draft, then send it for review. An editor with publishing rights will publish it.
                    @endswitch
                </p>
            @endif
        </x-admin.card>

        <x-admin.card title="Details">
            <div class="space-y-4">
                <x-admin.select name="news_category_id" label="Category">
                    <option value="">No category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('news_category_id', $article->news_category_id) === $category->id)>{{ $category->translate('name', 'en') }}{{ $category->is_active ? '' : ' (hidden)' }}</option>
                    @endforeach
                </x-admin.select>
                <x-admin.select name="news_author_id" label="Author (byline)">
                    <option value="">No byline</option>
                    @foreach($authors as $author)
                        <option value="{{ $author->id }}" @selected((int) old('news_author_id', $article->news_author_id) === $author->id)>{{ $author->name }}</option>
                    @endforeach
                </x-admin.select>
                <div>
                    <label for="slug" class="block text-sm font-medium text-ink">Web address</label>
                    <div class="mt-1.5 flex rounded-lg bg-surface shadow-sm ring-1 ring-line-strong focus-within:ring-2 focus-within:ring-primary">
                        <span class="flex items-center pl-3 text-sm text-ink-subtle">/news/</span>
                        <input id="slug" name="slug" value="{{ old('slug', $article->slug) }}" placeholder="from the headline" class="block w-full rounded-r-lg border-0 bg-transparent py-2.5 pr-3 text-sm text-ink focus:outline-none">
                    </div>
                    @error('slug')<p class="mt-1.5 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                    @if($isEditing && $article->isLive())
                        <a href="{{ $article->publicUrl() }}" target="_blank" class="mt-2 inline-block rounded text-sm font-medium text-primary hover:text-primary-hover">View live article &nearr;</a>
                    @endif
                </div>
                <label class="flex items-center gap-2.5 text-sm text-ink">
                    <input type="hidden" name="allow_comments" value="0">
                    <input type="checkbox" name="allow_comments" value="1" class="size-4 rounded accent-primary" @checked(old('allow_comments', $article->allow_comments))>
                    Allow reader comments
                </label>
            </div>
        </x-admin.card>

        <x-admin.card title="Cover image">
            @if($article->coverUrl())
                <img src="{{ $article->coverUrl() }}" alt="" class="mb-4 aspect-video w-full rounded-lg object-cover ring-1 ring-line">
            @endif
            <input name="cover_image" type="file" accept="image/*" aria-label="Cover image"
                   class="block w-full text-sm text-ink-muted file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-primary-soft file:px-3 file:py-2 file:text-sm file:font-semibold file:text-primary">
            <p class="mt-1.5 text-xs text-ink-subtle">16:9 works best, e.g. 1600 × 900 px.</p>
            @error('cover_image')<p class="mt-1.5 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
        </x-admin.card>

        <div class="flex flex-col gap-2">
            @if($canPublish)
                <x-admin.button class="w-full py-3" loading-text="Saving…">{{ $isEditing ? 'Save article' : 'Create article' }}</x-admin.button>
            @else
                <x-admin.button variant="secondary" class="w-full py-3" loading-text="Saving…">{{ $article->status === ArticleStatus::Published ? 'Save changes' : 'Save draft' }}</x-admin.button>
                @if($canSubmit)
                    <x-admin.button name="intent" value="submit" class="w-full py-3" loading-text="Sending…">Save &amp; send for review</x-admin.button>
                @endif
            @endif
        </div>
    </div>
</form>

@if($isEditing)
    @can(PermissionArea::News->permission(PermissionAction::Delete))
        <form method="POST" action="{{ route('admin.news.articles.destroy', $article) }}" class="mt-6" onsubmit="return confirm('Delete this article? It is removed from the website, homepage and search.')">
            @csrf @method('DELETE')
            <button type="submit" class="cursor-pointer rounded text-sm font-semibold text-danger hover:underline">Delete article</button>
        </form>
    @endcan
@endif
@endsection
