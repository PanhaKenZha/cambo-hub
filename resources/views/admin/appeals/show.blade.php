@extends('layouts.admin')
@section('title', 'Appeal #'.$appeal->id)
@section('content')
@php($subject = $appeal->appealable)
<a href="{{ route('admin.appeals.index') }}" class="text-sm font-medium text-slate-500 hover:text-brand-700">&larr; Appeals</a>

<div class="mt-3">
    <x-admin.badge :color="$appeal->status->color()">{{ $appeal->status->label() }}</x-admin.badge>
    <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Appeal from {{ $appeal->user->name }}</h1>
    <p class="mt-1 text-sm text-slate-500">Appeal #{{ $appeal->id }} · filed {{ $appeal->created_at->diffForHumans() }}</p>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <x-admin.card title="What they said">
            <p class="text-sm whitespace-pre-line text-slate-800">{{ $appeal->message }}</p>
        </x-admin.card>

        <x-admin.card title="Original decision">
            @if($subject instanceof \App\Models\ApprovalRequest)
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-slate-500">Rejected submission</dt><dd class="mt-1 font-medium"><a href="{{ route('admin.approvals.show', $subject) }}" class="text-brand-700 hover:underline">{{ $subject->title }}</a> <span class="text-slate-500">({{ $subject->module->label() }})</span></dd></div>
                    <div><dt class="text-slate-500">Reason given</dt><dd class="mt-1 text-slate-800">{{ $subject->rejection_reason }}</dd></div>
                    <div><dt class="text-slate-500">Decided by</dt><dd class="mt-1 text-slate-800">{{ $subject->reviewer?->name ?? 'unknown' }} · {{ $subject->reviewed_at?->toDayDateTimeString() }}</dd></div>
                </dl>
            @elseif($subject instanceof \App\Models\UserViolation)
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-slate-500">Penalty</dt><dd class="mt-1"><x-admin.badge :color="$subject->type->color()">{{ $subject->type->label() }}</x-admin.badge> <x-admin.badge :color="$subject->status->color()">{{ $subject->status->label() }}</x-admin.badge></dd></div>
                    <div><dt class="text-slate-500">Reason given</dt><dd class="mt-1 text-slate-800">{{ $subject->reason }}</dd></div>
                    <div><dt class="text-slate-500">Issued by</dt><dd class="mt-1 text-slate-800">{{ $subject->issuer?->name ?? 'unknown' }} · {{ $subject->created_at->toDayDateTimeString() }}</dd></div>
                </dl>
            @else
                <p class="text-sm text-slate-500">The original item no longer exists.</p>
            @endif
        </x-admin.card>
    </div>

    <div>
        @can('decide', $appeal)
            <x-admin.card title="Decision">
                <form method="POST" action="{{ route('admin.appeals.decide', $appeal) }}" class="space-y-4">
                    @csrf
                    <fieldset class="space-y-2">
                        <legend class="sr-only">Decision</legend>
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl p-3 ring-1 ring-slate-200 has-checked:bg-emerald-50 has-checked:ring-emerald-500">
                            <input type="radio" name="decision" value="overturn" class="mt-0.5 accent-emerald-600" required @checked(old('decision') === 'overturn')>
                            <span><span class="block text-sm font-medium text-slate-900">Accept appeal</span><span class="block text-xs text-slate-500">{{ $subject instanceof \App\Models\ApprovalRequest ? 'Approve the submission.' : 'Remove the penalty and restore access.' }}</span></span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl p-3 ring-1 ring-slate-200 has-checked:bg-slate-50 has-checked:ring-slate-500">
                            <input type="radio" name="decision" value="uphold" class="mt-0.5 accent-slate-600" required @checked(old('decision') === 'uphold')>
                            <span><span class="block text-sm font-medium text-slate-900">Decline appeal</span><span class="block text-xs text-slate-500">The original decision stands.</span></span>
                        </label>
                    </fieldset>
                    <div>
                        <label for="note" class="block text-sm font-medium text-slate-700">Note to the user</label>
                        <textarea id="note" name="note" rows="3" required class="mt-1.5 block w-full rounded-lg border-0 px-3.5 py-2.5 text-sm shadow-sm ring-1 ring-slate-300 ring-inset focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('note') }}</textarea>
                        @error('note')<p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <x-admin.button class="w-full">Save decision</x-admin.button>
                </form>
            </x-admin.card>
        @else
            <x-admin.card title="Decision">
                @if($appeal->isPending())
                    <p class="text-sm text-slate-500">
                        @if(auth()->id() === $appeal->originalDeciderId())
                            You made the original decision, so another moderator must decide this appeal.
                        @else
                            You don't have permission to decide this appeal.
                        @endif
                    </p>
                @else
                    <p class="text-sm text-slate-700">{{ $appeal->status->label() }} by <span class="font-medium">{{ $appeal->decider?->name ?? 'unknown' }}</span></p>
                    <p class="text-xs text-slate-500">{{ $appeal->decided_at?->toDayDateTimeString() }}</p>
                    @if($appeal->decision_note)<p class="mt-3 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700">{{ $appeal->decision_note }}</p>@endif
                @endif
            </x-admin.card>
        @endcan
    </div>
</div>
@endsection
