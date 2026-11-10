@extends('layouts.admin')
@section('title', 'News authors')
@section('content')
@include('admin.news._nav')

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-ink">Authors</h1>
        <p class="mt-1 text-sm text-ink-muted">Bylines shown on articles. Link an author to an account so their submissions and reports are traced to them.</p>
    </div>
    @can(\App\Enums\PermissionArea::News->permission(\App\Enums\PermissionAction::Create))
        <x-admin.button :href="route('admin.news.authors.create')">New author</x-admin.button>
    @endcan
</div>

@if($authors->isEmpty())
    <x-ui.empty-state class="mt-6" title="No authors yet." />
@else
    <x-ui.table label="News authors" class="mt-6">
        <x-slot:head>
            <tr>
                <th scope="col">Author</th>
                <th scope="col">Linked account</th>
                <th scope="col" class="text-right">Articles</th>
                <th scope="col">Status</th>
                <th scope="col"><span class="sr-only">Actions</span></th>
            </tr>
        </x-slot:head>
        @foreach($authors as $author)
            <tr class="hover:bg-surface-muted/60">
                <td data-label="Author">
                    <div class="flex items-center gap-3">
                        @if($author->avatarUrl())
                            <img src="{{ $author->avatarUrl() }}" alt="" class="size-9 rounded-full object-cover ring-1 ring-line">
                        @else
                            <span class="flex size-9 items-center justify-center rounded-full bg-primary-soft text-xs font-semibold text-primary">{{ mb_strtoupper(mb_substr($author->name, 0, 1)) }}</span>
                        @endif
                        <span class="font-medium text-ink">{{ $author->name }}</span>
                    </div>
                </td>
                <td data-label="Linked account" class="break-all">{{ $author->user?->email ?? '—' }}</td>
                <td data-label="Articles" class="text-right tabular-nums">{{ $author->articles_count }}</td>
                <td data-label="Status">@if($author->is_active)<x-admin.badge color="green">Active</x-admin.badge>@else<x-admin.badge color="gray">Inactive</x-admin.badge>@endif</td>
                <td class="text-right whitespace-nowrap">
                    @can(\App\Enums\PermissionArea::News->permission(\App\Enums\PermissionAction::Edit))
                        <a href="{{ route('admin.news.authors.edit', $author) }}" class="rounded text-sm font-semibold text-primary hover:text-primary-hover">Edit</a>
                    @endcan
                </td>
            </tr>
        @endforeach
    </x-ui.table>
    @if($authors->hasPages())<div class="mt-4">{{ $authors->links() }}</div>@endif
@endif
@endsection
