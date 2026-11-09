@extends('layouts.admin')
@section('title', 'Appeal #'.$appeal->id)
@section('content')
@php($subject = $appeal->appealable)
<a href="{{ route('admin.appeals.index') }}" class="rounded text-sm font-medium text-ink-muted hover:text-primary">&larr; Appeals</a>

<div class="mt-3">
    <x-admin.badge :color="$appeal->status->color()">{{ $appeal->status->label() }}</x-admin.badge>
    <h1 class="mt-2 text-2xl font-bold tracking-tight break-words text-ink">Appeal from {{ $appeal->user->name }}</h1>
    <p class="mt-1 text-sm text-ink-muted">Appeal #{{ $appeal->id }} · filed {{ $appeal->created_at->diffForHumans() }}</p>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <x-admin.card title="What they said">
            <p class="text-sm break-words whitespace-pre-line text-ink">{{ $appeal->message }}</p>
        </x-admin.card>

        <x-admin.card title="Original decision">
            @if($subject instanceof \App\Models\ApprovalRequest)
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-ink-muted">Rejected submission</dt><dd class="mt-1 font-medium"><a href="{{ route('admin.approvals.show', $subject) }}" class="rounded text-primary hover:underline">{{ $subject->title }}</a> <span class="text-ink-muted">({{ $subject->module->label() }})</span></dd></div>
                    <div><dt class="text-ink-muted">Reason given</dt><dd class="mt-1 text-ink">{{ $subject->rejection_reason }}</dd></div>
                    <div><dt class="text-ink-muted">Decided by</dt><dd class="mt-1 text-ink">{{ $subject->reviewer?->name ?? 'unknown' }} · {{ $subject->reviewed_at?->toDayDateTimeString() }}</dd></div>
                </dl>
            @elseif($subject instanceof \App\Models\UserViolation)
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-ink-muted">Penalty</dt><dd class="mt-1"><x-admin.badge :color="$subject->type->color()">{{ $subject->type->label() }}</x-admin.badge> <x-admin.badge :color="$subject->status->color()">{{ $subject->status->label() }}</x-admin.badge></dd></div>
                    <div><dt class="text-ink-muted">Reason given</dt><dd class="mt-1 text-ink">{{ $subject->reason }}</dd></div>
                    <div><dt class="text-ink-muted">Issued by</dt><dd class="mt-1 text-ink">{{ $subject->issuer?->name ?? 'unknown' }} · {{ $subject->created_at->toDayDateTimeString() }}</dd></div>
                </dl>
            @else
                <p class="text-sm text-ink-muted">The original item no longer exists.</p>
            @endif
        </x-admin.card>
    </div>

    <div>
        @can('decide', $appeal)
            <x-admin.card title="Decision">
                <form method="POST" action="{{ route('admin.appeals.decide', $appeal) }}" class="space-y-4" data-loading>
                    <x-ui.error-summary />
                    @csrf
                    <fieldset id="decision" tabindex="-1" class="space-y-2 focus:outline-none" @error('decision') aria-describedby="decision-error" @enderror>
                        <legend class="sr-only">Decision</legend>
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl p-3 ring-1 ring-line-strong hover:bg-surface-muted has-checked:bg-success-soft has-checked:ring-success">
                            <input type="radio" name="decision" value="overturn" class="mt-0.5 accent-emerald-600 dark:accent-emerald-400" required @checked(old('decision') === 'overturn')>
                            <span><span class="block text-sm font-medium text-ink">Accept appeal</span><span class="block text-xs text-ink-muted">{{ $subject instanceof \App\Models\ApprovalRequest ? 'Approve the submission.' : 'Remove the penalty and restore access.' }}</span></span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl p-3 ring-1 ring-line-strong hover:bg-surface-muted has-checked:bg-surface-muted has-checked:ring-ink-muted">
                            <input type="radio" name="decision" value="uphold" class="mt-0.5 accent-slate-600 dark:accent-slate-300" required @checked(old('decision') === 'uphold')>
                            <span><span class="block text-sm font-medium text-ink">Decline appeal</span><span class="block text-xs text-ink-muted">The original decision stands.</span></span>
                        </label>
                        @error('decision')<p id="decision-error" class="text-sm font-medium text-danger" role="alert">{{ $message }}</p>@enderror
                    </fieldset>
                    <div>
                        <label for="note" class="block text-sm font-medium text-ink">Note to the user</label>
                        <textarea id="note" name="note" rows="3" required @error('note') aria-invalid="true" aria-describedby="note-error" @enderror class="form-control mt-1.5 rounded-lg shadow-sm">{{ old('note') }}</textarea>
                        @error('note')<p id="note-error" class="mt-1.5 text-sm font-medium text-danger" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <x-admin.button class="w-full" loading-text="Saving…">Save decision</x-admin.button>
                </form>
            </x-admin.card>
        @else
            <x-admin.card title="Decision">
                @if($appeal->isPending())
                    <p class="text-sm text-ink-muted">
                        @if(auth()->id() === $appeal->originalDeciderId())
                            You made the original decision, so another moderator must decide this appeal.
                        @else
                            You don't have permission to decide this appeal.
                        @endif
                    </p>
                @else
                    <p class="text-sm text-ink">{{ $appeal->status->label() }} by <span class="font-medium">{{ $appeal->decider?->name ?? 'unknown' }}</span></p>
                    <p class="text-xs text-ink-muted">{{ $appeal->decided_at?->toDayDateTimeString() }}</p>
                    @if($appeal->decision_note)<p class="mt-3 rounded-lg bg-surface-muted px-3 py-2 text-sm break-words text-ink">{{ $appeal->decision_note }}</p>@endif
                @endif
            </x-admin.card>
        @endcan
    </div>
</div>
@endsection
