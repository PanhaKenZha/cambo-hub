@extends('layouts.admin')
@section('title', 'News comments')
@section('content')
@php
    use App\Enums\CommentStatus;
    use App\Enums\PermissionAction;
    use App\Enums\PermissionArea;

    $canModerate = auth()->user()->can(PermissionArea::News->permission(PermissionAction::Manage));
@endphp
@include('admin.news._nav')

<div>
    <h1 class="text-2xl font-bold tracking-tight text-ink">Comments</h1>
    <p class="mt-1 text-sm text-ink-muted">Reader comments appear on articles only after they are approved. Oldest waiting comments first.</p>
</div>

<div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <nav class="flex gap-1" aria-label="Filter by status">
        @foreach(CommentStatus::cases() as $status)
            @php($isActive = $status === $activeStatus)
            <a href="{{ route('admin.news.comments.index', ['status' => $status->value]) }}" @if($isActive) aria-current="page" @endif
               @class(['flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium', 'bg-primary-soft text-primary ring-1 ring-primary/20' => $isActive, 'text-ink-muted hover:bg-surface-muted hover:text-ink' => ! $isActive])>
                {{ $status->label() }} <span class="text-xs tabular-nums opacity-70">{{ $statusCounts[$status->value] ?? 0 }}</span>
            </a>
        @endforeach
    </nav>
    <form method="GET" action="{{ route('admin.news.comments.index') }}" role="search" class="flex gap-2">
        <input type="hidden" name="status" value="{{ $activeStatus->value }}">
        <label for="search" class="sr-only">Search comments</label>
        <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search comments" class="form-control rounded-lg shadow-sm sm:w-64">
        <x-admin.button variant="secondary">Search</x-admin.button>
    </form>
</div>

<x-ui.error-summary class="mt-4" />

@if($comments->isEmpty())
    <x-ui.empty-state class="mt-4" :title="$activeStatus === CommentStatus::Pending ? 'No comments waiting. All caught up.' : 'No comments here.'" />
@else
    <form method="POST" action="{{ route('admin.news.comments.bulk') }}" id="bulk-form">@csrf</form>

    @if($canModerate)
        <div class="mt-4 flex flex-wrap items-center gap-2 rounded-xl bg-surface-muted px-4 py-2.5 text-sm">
            <label class="flex items-center gap-2 text-ink-muted"><input type="checkbox" class="size-4 rounded accent-primary" onclick="document.querySelectorAll('[data-comment-checkbox]').forEach((box) => box.checked = this.checked)"> Select all</label>
            <span class="text-ink-subtle">·</span>
            @if($activeStatus !== CommentStatus::Approved)<button type="submit" form="bulk-form" name="action" value="approve" class="cursor-pointer rounded-md px-2.5 py-1 font-semibold text-success hover:bg-success-soft">Approve selected</button>@endif
            @if($activeStatus !== CommentStatus::Hidden)<button type="submit" form="bulk-form" name="action" value="hide" class="cursor-pointer rounded-md px-2.5 py-1 font-semibold text-ink-muted hover:bg-surface">Hide selected</button>@endif
            <button type="submit" form="bulk-form" name="action" value="delete" class="cursor-pointer rounded-md px-2.5 py-1 font-semibold text-danger hover:bg-danger-soft" onclick="return confirm('Delete the selected comments?')">Delete selected</button>
        </div>
    @endif

    <ul class="mt-3 space-y-3">
        @foreach($comments as $comment)
            <li class="flex gap-4 rounded-2xl bg-surface p-4 shadow-card ring-1 ring-line">
                @if($canModerate)
                    <input type="checkbox" name="ids[]" value="{{ $comment->id }}" form="bulk-form" data-comment-checkbox aria-label="Select comment" class="mt-1 size-4 shrink-0 rounded accent-primary">
                @endif
                <div class="min-w-0 flex-1">
                    <p class="text-sm whitespace-pre-line text-ink">{{ $comment->body }}</p>
                    <p class="mt-2 text-xs text-ink-subtle">
                        {{ $comment->user?->name ?? 'Deleted user' }} · {{ $comment->created_at->diffForHumans() }} · on
                        <a href="{{ route('admin.news.articles.edit', $comment->article) }}" class="font-medium text-primary hover:underline">{{ \Illuminate\Support\Str::limit($comment->article->translate('title', 'en'), 60) }}</a>
                        @if($comment->moderator) · {{ $comment->status->label() }} by {{ $comment->moderator->name }}@endif
                    </p>
                </div>
                @if($canModerate)
                    <div class="flex shrink-0 items-start gap-1">
                        @foreach(['approve' => ['Approve', 'text-success hover:bg-success-soft'], 'hide' => ['Hide', 'text-ink-muted hover:bg-surface-muted'], 'delete' => ['Delete', 'text-danger hover:bg-danger-soft']] as $action => [$label, $classes])
                            @continue(($action === 'approve' && $comment->status === CommentStatus::Approved) || ($action === 'hide' && $comment->status === CommentStatus::Hidden))
                            <form method="POST" action="{{ route('admin.news.comments.moderate', $comment) }}" @if($action === 'delete') onsubmit="return confirm('Delete this comment?')" @endif>
                                @csrf <input type="hidden" name="action" value="{{ $action }}">
                                <button type="submit" class="cursor-pointer rounded-md px-2.5 py-1 text-sm font-semibold {{ $classes }}">{{ $label }}</button>
                            </form>
                        @endforeach
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
    @if($comments->hasPages())<div class="mt-4">{{ $comments->links() }}</div>@endif
@endif
@endsection
