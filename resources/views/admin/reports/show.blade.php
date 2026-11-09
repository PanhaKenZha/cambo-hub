@extends('layouts.admin')
@section('title', 'Report #'.$report->id)
@section('content')
<a href="{{ route('admin.reports.index') }}" class="rounded text-sm font-medium text-ink-muted hover:text-primary">&larr; Content reports</a>

<div class="mt-3">
    <div class="flex flex-wrap items-center gap-2">
        <x-admin.badge color="red">{{ $report->reason->label() }}</x-admin.badge>
        <x-admin.badge :color="$report->status->color()">{{ $report->status->label() }}</x-admin.badge>
        @if($report->content_taken_down)<x-admin.badge color="gray">Content taken down</x-admin.badge>@endif
    </div>
    <h1 class="mt-2 text-2xl font-bold tracking-tight break-words text-ink">{{ $report->title }}</h1>
    <p class="mt-1 text-sm text-ink-muted">Report #{{ $report->id }} · {{ $report->module?->label() ?? class_basename($report->reportable_type) }} · {{ $report->created_at->diffForHumans() }}</p>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <x-admin.card title="Report">
            <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                <div><dt class="text-ink-muted">Reported by</dt><dd class="mt-1 font-medium text-ink">{{ $report->reporter?->name ?? 'Anonymous' }}</dd></div>
                <div><dt class="text-ink-muted">Content owner</dt><dd class="mt-1 font-medium text-ink">{{ $owner?->name ?? 'Unknown' }}</dd><dd class="break-all text-ink-muted">{{ $owner?->email }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-ink-muted">Details</dt><dd class="mt-1 whitespace-pre-line text-ink">{{ $report->details ?: 'No details given.' }}</dd></div>
                @if($otherReportCount > 0)
                    <div class="rounded-xl bg-warning-soft p-3 text-ink ring-1 ring-warning/30 sm:col-span-2">This content has been reported {{ $otherReportCount }} other {{ \Illuminate\Support\Str::plural('time', $otherReportCount) }}. Resolving this report resolves the open ones too.</div>
                @endif
                @unless($report->isOpen())
                    <div class="rounded-xl bg-surface-muted p-4 ring-1 ring-line sm:col-span-2">
                        <dt class="font-medium text-ink">{{ $report->status->label() }} by {{ $report->handler?->name ?? 'unknown' }} · {{ $report->handled_at?->toDayDateTimeString() }}</dt>
                        <dd class="mt-1 text-ink-muted">{{ $report->resolution_note }}</dd>
                        @if($report->violation)<dd class="mt-2"><x-admin.badge :color="$report->violation->type->color()">{{ $report->violation->type->label() }} issued</x-admin.badge></dd>@endif
                    </div>
                @endunless
            </dl>
        </x-admin.card>

        @if($owner)
            <x-admin.card title="Owner's violation history">
                @forelse($ownerViolations as $violation)
                    <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 border-b border-line py-2.5 text-sm last:border-0">
                        <div>
                            <x-admin.badge :color="$violation->type->color()">{{ $violation->type->label() }}</x-admin.badge>
                            <span class="ml-2 text-ink">{{ \Illuminate\Support\Str::limit($violation->reason, 80) }}</span>
                        </div>
                        <span class="shrink-0 text-xs text-ink-muted">{{ $violation->created_at->diffForHumans() }} · {{ $violation->status->label() }}</span>
                    </div>
                @empty
                    <p class="text-sm text-ink-muted">No previous violations.</p>
                @endforelse
            </x-admin.card>
        @endif
    </div>

    <div class="space-y-6">
        @can('handle', $report)
            <x-admin.card title="Take action">
                <form method="POST" action="{{ route('admin.reports.resolve', $report) }}" class="space-y-4" id="resolve-form" data-loading>
                    <x-ui.error-summary />
                    @csrf
                    <div>
                        <label for="note" class="block text-sm font-medium text-ink">Decision note</label>
                        <textarea id="note" name="note" rows="3" required @error('note') aria-invalid="true" aria-describedby="note-error" @enderror class="form-control mt-1.5 rounded-lg shadow-sm" placeholder="What did you find?">{{ old('note') }}</textarea>
                        @error('note')<p id="note-error" class="mt-1.5 text-sm font-medium text-danger" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <label class="flex items-center gap-2.5 text-sm text-ink">
                        <input type="hidden" name="take_down" value="0">
                        <input type="checkbox" id="take_down" name="take_down" value="1" class="size-4 cursor-pointer rounded accent-brand-600 dark:accent-brand-300" @checked(old('take_down'))>
                        Take the content down
                    </label>
                    @if($owner)
                        <x-admin.select name="violation_type" label="Penalty for the owner" onchange="document.getElementById('suspension-days').hidden = this.value !== 'suspension'">
                            <option value="">No penalty</option>
                            @foreach(\App\Enums\ViolationType::cases() as $type)
                                <option value="{{ $type->value }}" @selected(old('violation_type') === $type->value)>{{ $type->label() }}</option>
                            @endforeach
                        </x-admin.select>
                        <div id="suspension-days" @if(old('violation_type') !== 'suspension') hidden @endif>
                            <x-admin.input name="suspension_days" type="number" label="Suspension length (days)" min="1" max="365" :value="7" />
                        </div>
                    @endif
                    <x-admin.button variant="danger" class="w-full" loading-text="Resolving…">Resolve report</x-admin.button>
                </form>

                <form method="POST" action="{{ route('admin.reports.dismiss', $report) }}" class="mt-6 space-y-3 border-t border-line pt-6" data-loading>
                    <x-ui.error-summary bag="dismissal" :ids="['note' => 'dismiss_note']" />
                    @csrf
                    <x-admin.input name="note" id="dismiss_note" label="No problem found? Dismiss with a note" error-bag="dismissal" placeholder="e.g. Content follows the rules" />
                    <x-admin.button variant="secondary" class="w-full" loading-text="Dismissing…">Dismiss report</x-admin.button>
                </form>
            </x-admin.card>
        @endcan
    </div>
</div>
@endsection
