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
<a href="{{ route('admin.approvals.index') }}" class="rounded text-sm font-medium text-ink-muted hover:text-primary">&larr; Approval center</a>

<div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <div class="flex flex-wrap items-center gap-2">
            <x-admin.badge>{{ $approvalRequest->module->label() }}</x-admin.badge>
            <x-admin.badge :color="$approvalRequest->status->color()">{{ $approvalRequest->status->label() }}</x-admin.badge>
        </div>
        <h1 class="mt-2 text-2xl font-bold tracking-tight break-words text-ink">{{ $approvalRequest->title }}</h1>
        <p class="mt-1 text-sm text-ink-muted">Submission #{{ $approvalRequest->id }} · submitted {{ $approvalRequest->created_at->diffForHumans() }} by {{ $approvalRequest->submitter?->name ?? 'a deleted user' }}</p>
    </div>
    @if($previewUrl)
        <x-admin.button variant="secondary" :href="$previewUrl" target="_blank" rel="noopener">Preview content &nearr;</x-admin.button>
    @endif
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <x-admin.card title="Submission">
            <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                <div><dt class="text-ink-muted">Submitted by</dt><dd class="mt-1 font-medium text-ink">{{ $approvalRequest->submitter?->name ?? 'Deleted user' }}</dd><dd class="break-all text-ink-muted">{{ $approvalRequest->submitter?->email }}</dd></div>
                <div><dt class="text-ink-muted">Submitted</dt><dd class="mt-1 font-medium text-ink">{{ $approvalRequest->created_at->toDayDateTimeString() }}</dd></div>
                <div class="sm:col-span-2">
                    <dt class="text-ink-muted">Summary</dt>
                    <dd class="mt-1 whitespace-pre-line text-ink">{{ $approvalRequest->summary ?: 'No summary provided.' }}</dd>
                </div>
                @if($approvalRequest->rejection_reason)
                    <div class="rounded-xl bg-danger-soft p-4 ring-1 ring-danger/25 sm:col-span-2">
                        <dt class="font-medium text-danger">Rejection reason</dt>
                        <dd class="mt-1 text-ink">{{ $approvalRequest->rejection_reason }}</dd>
                    </div>
                @endif
            </dl>
        </x-admin.card>

        <x-admin.card title="Review history">
            <ol class="relative space-y-6 border-l border-line pl-6">
                @foreach($approvalRequest->events as $event)
                    <li class="relative">
                        <span @class([
                            'absolute -left-[31px] flex size-4 items-center justify-center rounded-full ring-4 ring-surface',
                            'bg-success' => in_array($event->action, ['approved', 'appeal_overturned'], true),
                            'bg-danger' => $event->action === 'rejected',
                            'bg-primary' => ! in_array($event->action, ['approved', 'appeal_overturned', 'rejected'], true),
                        ]) aria-hidden="true"></span>
                        <p class="text-sm font-medium text-ink">{{ $event->label() }}</p>
                        <p class="text-xs text-ink-muted">{{ $event->actor?->name ?? 'System' }} · {{ $event->created_at->toDayDateTimeString() }}</p>
                        @if($event->note)<p class="mt-1.5 rounded-lg bg-surface-muted px-3 py-2 text-sm break-words text-ink">{{ $event->note }}</p>@endif
                    </li>
                @endforeach
            </ol>
        </x-admin.card>

        @if($approvalRequest->appeals->isNotEmpty())
            <x-admin.card title="Appeals">
                <ul class="divide-y divide-line">
                    @foreach($approvalRequest->appeals as $appeal)
                        <li class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 py-3 text-sm">
                            <a href="{{ route('admin.appeals.show', $appeal) }}" class="rounded text-ink hover:text-primary">Appeal by {{ $appeal->user->name }} · {{ $appeal->created_at->diffForHumans() }}</a>
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
            <x-ui.error-summary />
            <x-admin.card title="Reviewer">
                <form method="POST" action="{{ route('admin.approvals.assign', $approvalRequest) }}" class="space-y-3" data-loading>
                    @csrf
                    <x-admin.select name="assignee" label="Assigned to">
                        <option value="">Choose a reviewer…</option>
                        @foreach($reviewers as $reviewer)
                            <option value="{{ $reviewer->id }}" @selected($approvalRequest->assigned_to === $reviewer->id)>{{ $reviewer->name }}{{ $reviewer->is(auth()->user()) ? ' (me)' : '' }}</option>
                        @endforeach
                    </x-admin.select>
                    <x-admin.button variant="secondary" class="w-full" loading-text="Assigning…">Assign</x-admin.button>
                </form>
            </x-admin.card>

            <x-admin.card title="Decision">
                <form method="POST" action="{{ route('admin.approvals.approve', $approvalRequest) }}" class="space-y-3" data-loading>
                    @csrf
                    <x-admin.input name="note" label="Note (optional)" placeholder="Visible in the review history" />
                    <x-admin.button class="w-full bg-emerald-600! text-white! hover:bg-emerald-700! focus-visible:outline-emerald-600! dark:bg-emerald-400! dark:text-emerald-950! dark:hover:bg-emerald-300! dark:focus-visible:outline-emerald-300!" loading-text="Approving…">Approve</x-admin.button>
                </form>

                <form method="POST" action="{{ route('admin.approvals.reject', $approvalRequest) }}" class="mt-6 space-y-3 border-t border-line pt-6" data-loading>
                    @php($rejectionError = $errors->getBag('rejection')->first('reason') ?: null)
                    <x-ui.error-summary bag="rejection" />
                    @csrf
                    <label for="reason" class="block text-sm font-medium text-ink">Rejection reason</label>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($rejectionPresets as $preset)
                            <button type="button" class="cursor-pointer rounded-md bg-surface-muted px-2 py-1 text-left text-xs text-ink-muted ring-1 ring-line hover:bg-primary-soft hover:text-primary"
                                    onclick="document.getElementById('reason').value = this.textContent.trim()">{{ $preset }}</button>
                        @endforeach
                    </div>
                    <textarea id="reason" name="reason" rows="3" required minlength="5" placeholder="Explain what needs to change. The submitter will see this."
                              @if($rejectionError) aria-invalid="true" aria-describedby="reason-error" @endif
                              class="form-control rounded-lg shadow-sm">{{ old('reason') }}</textarea>
                    @if($rejectionError)<p id="reason-error" class="text-sm font-medium text-danger" role="alert">{{ $rejectionError }}</p>@endif
                    <x-admin.button variant="danger" class="w-full" loading-text="Rejecting…">Reject</x-admin.button>
                </form>
            </x-admin.card>
        @else
            <x-admin.card title="Decision">
                @if($approvalRequest->isPending())
                    <p class="text-sm text-ink-muted">You can view this submission but not decide it. Reviewing {{ $approvalRequest->module->label() }} content needs the approvals "manage" permission and the {{ $approvalRequest->module->label() }} "manage" permission.</p>
                @else
                    <p class="text-sm text-ink">{{ $approvalRequest->status->label() }} by <span class="font-medium">{{ $approvalRequest->reviewer?->name ?? 'unknown' }}</span></p>
                    <p class="text-xs text-ink-muted">{{ $approvalRequest->reviewed_at?->toDayDateTimeString() }}</p>
                @endif
            </x-admin.card>
        @endcan
    </div>
</div>
@endsection
