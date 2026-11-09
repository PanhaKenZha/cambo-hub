@extends('layouts.admin')
@section('title', 'Report #'.$report->id)
@section('content')
<a href="{{ route('admin.reports.index') }}" class="text-sm font-medium text-slate-500 hover:text-brand-700">&larr; Content reports</a>

<div class="mt-3">
    <div class="flex flex-wrap items-center gap-2">
        <x-admin.badge color="red">{{ $report->reason->label() }}</x-admin.badge>
        <x-admin.badge :color="$report->status->color()">{{ $report->status->label() }}</x-admin.badge>
        @if($report->content_taken_down)<x-admin.badge color="gray">Content taken down</x-admin.badge>@endif
    </div>
    <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $report->title }}</h1>
    <p class="mt-1 text-sm text-slate-500">Report #{{ $report->id }} · {{ $report->module?->label() ?? class_basename($report->reportable_type) }} · {{ $report->created_at->diffForHumans() }}</p>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <x-admin.card title="Report">
            <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">Reported by</dt><dd class="mt-1 font-medium text-slate-900">{{ $report->reporter?->name ?? 'Anonymous' }}</dd></div>
                <div><dt class="text-slate-500">Content owner</dt><dd class="mt-1 font-medium text-slate-900">{{ $owner?->name ?? 'Unknown' }}</dd><dd class="text-slate-500">{{ $owner?->email }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-slate-500">Details</dt><dd class="mt-1 whitespace-pre-line text-slate-800">{{ $report->details ?: 'No details given.' }}</dd></div>
                @if($otherReportCount > 0)
                    <div class="rounded-xl bg-amber-50 p-3 text-amber-900 ring-1 ring-amber-600/20 sm:col-span-2">This content has been reported {{ $otherReportCount }} other {{ \Illuminate\Support\Str::plural('time', $otherReportCount) }}. Resolving this report resolves the open ones too.</div>
                @endif
                @unless($report->isOpen())
                    <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200 sm:col-span-2">
                        <dt class="font-medium text-slate-800">{{ $report->status->label() }} by {{ $report->handler?->name ?? 'unknown' }} · {{ $report->handled_at?->toDayDateTimeString() }}</dt>
                        <dd class="mt-1 text-slate-700">{{ $report->resolution_note }}</dd>
                        @if($report->violation)<dd class="mt-2"><x-admin.badge :color="$report->violation->type->color()">{{ $report->violation->type->label() }} issued</x-admin.badge></dd>@endif
                    </div>
                @endunless
            </dl>
        </x-admin.card>

        @if($owner)
            <x-admin.card title="Owner's violation history">
                @forelse($ownerViolations as $violation)
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 py-2.5 text-sm last:border-0">
                        <div>
                            <x-admin.badge :color="$violation->type->color()">{{ $violation->type->label() }}</x-admin.badge>
                            <span class="ml-2 text-slate-700">{{ \Illuminate\Support\Str::limit($violation->reason, 80) }}</span>
                        </div>
                        <span class="shrink-0 text-xs text-slate-500">{{ $violation->created_at->diffForHumans() }} · {{ $violation->status->label() }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No previous violations.</p>
                @endforelse
            </x-admin.card>
        @endif
    </div>

    <div class="space-y-6">
        @can('handle', $report)
            <x-admin.card title="Take action">
                <form method="POST" action="{{ route('admin.reports.resolve', $report) }}" class="space-y-4" id="resolve-form">
                    @csrf
                    <div>
                        <label for="note" class="block text-sm font-medium text-slate-700">Decision note</label>
                        <textarea id="note" name="note" rows="3" required class="mt-1.5 block w-full rounded-lg border-0 px-3.5 py-2.5 text-sm shadow-sm ring-1 ring-slate-300 ring-inset focus:ring-2 focus:ring-brand-500 focus:outline-none" placeholder="What did you find?">{{ old('note') }}</textarea>
                        @error('note')<p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <label class="flex items-center gap-2.5 text-sm text-slate-700">
                        <input type="hidden" name="take_down" value="0">
                        <input type="checkbox" name="take_down" value="1" class="size-4 rounded accent-brand-600" @checked(old('take_down'))>
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
                    <x-admin.button variant="danger" class="w-full">Resolve report</x-admin.button>
                </form>

                <form method="POST" action="{{ route('admin.reports.dismiss', $report) }}" class="mt-6 space-y-3 border-t border-slate-100 pt-6">
                    @csrf
                    <x-admin.input name="note" id="dismiss_note" label="No problem found? Dismiss with a note" error-bag="dismissal" placeholder="e.g. Content follows the rules" />
                    <x-admin.button variant="secondary" class="w-full">Dismiss report</x-admin.button>
                </form>
            </x-admin.card>
        @endcan
    </div>
</div>
@endsection
