@extends('layouts.admin')
@section('title', 'Review #'.$approvalRequest->id)
@section('content')
@php
    $rejectionPresets = [
        'Incomplete information — please add the missing details.',
        'Does not meet the publishing guidelines.',
        'Duplicate of existing content.',
        'Inappropriate or offensive content.',
        'Suspected spam or scam.',
    ];
@endphp
<a href="{{ route('admin.approvals.index') }}" class="text-sm font-medium text-slate-500 hover:text-brand-700">&larr; Approval center</a>

<div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <div class="flex flex-wrap items-center gap-2">
            <x-admin.badge>{{ $approvalRequest->module->label() }}</x-admin.badge>
            <x-admin.badge :color="$approvalRequest->status->color()">{{ $approvalRequest->status->label() }}</x-admin.badge>
        </div>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $approvalRequest->title }}</h1>
        <p class="mt-1 text-sm text-slate-500">Submission #{{ $approvalRequest->id }} · submitted {{ $approvalRequest->created_at->diffForHumans() }} by {{ $approvalRequest->submitter?->name ?? 'a deleted user' }}</p>
    </div>
    @if($previewUrl)
        <x-admin.button variant="secondary" :href="$previewUrl" target="_blank" rel="noopener">Preview content &nearr;</x-admin.button>
    @endif
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <x-admin.card title="Submission">
            <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">Submitted by</dt><dd class="mt-1 font-medium text-slate-900">{{ $approvalRequest->submitter?->name ?? 'Deleted user' }}</dd><dd class="text-slate-500">{{ $approvalRequest->submitter?->email }}</dd></div>
                <div><dt class="text-slate-500">Submitted</dt><dd class="mt-1 font-medium text-slate-900">{{ $approvalRequest->created_at->toDayDateTimeString() }}</dd></div>
                <div class="sm:col-span-2">
                    <dt class="text-slate-500">Summary</dt>
                    <dd class="mt-1 whitespace-pre-line text-slate-800">{{ $approvalRequest->summary ?: 'No summary provided.' }}</dd>
                </div>
                @if($approvalRequest->rejection_reason)
                    <div class="rounded-xl bg-red-50 p-4 ring-1 ring-red-100 sm:col-span-2">
                        <dt class="font-medium text-red-800">Rejection reason</dt>
                        <dd class="mt-1 text-red-900">{{ $approvalRequest->rejection_reason }}</dd>
                    </div>
                @endif
            </dl>
        </x-admin.card>

        <x-admin.card title="Review history">
            <ol class="relative space-y-6 border-l border-slate-200 pl-6">
                @foreach($approvalRequest->events as $event)
                    <li class="relative">
                        <span @class([
                            'absolute -left-[31px] flex size-4 items-center justify-center rounded-full ring-4 ring-white',
                            'bg-emerald-500' => in_array($event->action, ['approved', 'appeal_overturned'], true),
                            'bg-red-500' => $event->action === 'rejected',
                            'bg-brand-500' => ! in_array($event->action, ['approved', 'appeal_overturned', 'rejected'], true),
                        ])></span>
                        <p class="text-sm font-medium text-slate-900">{{ $event->label() }}</p>
                        <p class="text-xs text-slate-500">{{ $event->actor?->name ?? 'System' }} · {{ $event->created_at->toDayDateTimeString() }}</p>
                        @if($event->note)<p class="mt-1.5 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700">{{ $event->note }}</p>@endif
                    </li>
                @endforeach
            </ol>
        </x-admin.card>

        @if($approvalRequest->appeals->isNotEmpty())
            <x-admin.card title="Appeals">
                <ul class="divide-y divide-slate-100">
                    @foreach($approvalRequest->appeals as $appeal)
                        <li class="flex items-center justify-between gap-4 py-3 text-sm">
                            <a href="{{ route('admin.appeals.show', $appeal) }}" class="text-slate-700 hover:text-brand-700">Appeal by {{ $appeal->user->name }} · {{ $appeal->created_at->diffForHumans() }}</a>
                            <x-admin.badge :color="$appeal->status->color()">{{ $appeal->status->label() }}</x-admin.badge>
                        </li>
                    @endforeach
                </ul>
            </x-admin.card>
        @endif
    </div>

    {{-- Decision panel --}}
    <div class="space-y-6">
        @can('review', $approvalRequest)
            <x-admin.card title="Reviewer">
                <form method="POST" action="{{ route('admin.approvals.assign', $approvalRequest) }}" class="space-y-3">
                    @csrf
                    <x-admin.select name="assignee" label="Assigned to">
                        <option value="">Choose a reviewer…</option>
                        @foreach($reviewers as $reviewer)
                            <option value="{{ $reviewer->id }}" @selected($approvalRequest->assigned_to === $reviewer->id)>{{ $reviewer->name }}{{ $reviewer->is(auth()->user()) ? ' (me)' : '' }}</option>
                        @endforeach
                    </x-admin.select>
                    <x-admin.button variant="secondary" class="w-full">Assign</x-admin.button>
                </form>
            </x-admin.card>

            <x-admin.card title="Decision">
                <form method="POST" action="{{ route('admin.approvals.approve', $approvalRequest) }}" class="space-y-3">
                    @csrf
                    <x-admin.input name="note" label="Note (optional)" placeholder="Visible in the review history" />
                    <x-admin.button class="w-full bg-emerald-600! hover:bg-emerald-700!">Approve</x-admin.button>
                </form>

                <form method="POST" action="{{ route('admin.approvals.reject', $approvalRequest) }}" class="mt-6 space-y-3 border-t border-slate-100 pt-6">
                    @csrf
                    <label for="reason" class="block text-sm font-medium text-slate-700">Rejection reason</label>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($rejectionPresets as $preset)
                            <button type="button" class="cursor-pointer rounded-md bg-slate-100 px-2 py-1 text-left text-xs text-slate-600 hover:bg-slate-200"
                                    onclick="document.getElementById('reason').value = this.textContent.trim()">{{ $preset }}</button>
                        @endforeach
                    </div>
                    <textarea id="reason" name="reason" rows="3" required minlength="5" placeholder="Explain what needs to change. The submitter will see this."
                              class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-slate-300 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-red-500 focus:outline-none">{{ old('reason') }}</textarea>
                    @error('reason', 'rejection')<p class="text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
                    <x-admin.button variant="danger" class="w-full">Reject</x-admin.button>
                </form>
            </x-admin.card>
        @else
            <x-admin.card title="Decision">
                @if($approvalRequest->isPending())
                    <p class="text-sm text-slate-500">You can view this submission but not decide it. Reviewing {{ $approvalRequest->module->label() }} content needs the approvals "manage" permission and the {{ $approvalRequest->module->label() }} "manage" permission.</p>
                @else
                    <p class="text-sm text-slate-700">{{ $approvalRequest->status->label() }} by <span class="font-medium">{{ $approvalRequest->reviewer?->name ?? 'unknown' }}</span></p>
                    <p class="text-xs text-slate-500">{{ $approvalRequest->reviewed_at?->toDayDateTimeString() }}</p>
                @endif
            </x-admin.card>
        @endcan
    </div>
</div>
@endsection
