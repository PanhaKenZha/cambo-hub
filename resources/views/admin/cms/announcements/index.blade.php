@extends('layouts.admin')
@section('title', 'Announcements')
@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Announcements</h1>
        <p class="mt-1 text-sm text-slate-500">Notices shown at the top of the public website, e.g. holidays, new features or service updates.</p>
    </div>
    @can(\App\Enums\PermissionArea::WebsiteContent->permission(\App\Enums\PermissionAction::Create))
        <x-admin.button :href="route('admin.cms.announcements.create')">New announcement</x-admin.button>
    @endcan
</div>

<div class="mt-6 space-y-3">
    @forelse($announcements as $announcement)
        <article class="flex items-start gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <span @class(['mt-1 size-2.5 shrink-0 rounded-full', 'bg-brand-500' => $announcement->level->value === 'info', 'bg-emerald-500' => $announcement->level->value === 'success', 'bg-amber-500' => $announcement->level->value === 'warning'])></span>
            <div class="min-w-0 flex-1">
                <p class="text-sm text-slate-900">{{ $announcement->translate('message', 'en') }}</p>
                @if(filled($announcement->message['km'] ?? null))<p class="mt-0.5 text-sm text-slate-500">{{ $announcement->message['km'] }}</p>@endif
                <p class="mt-2 text-xs text-slate-500">
                    {{ $announcement->starts_at ? 'From '.$announcement->starts_at->toDayDateTimeString() : 'Starts immediately' }}
                    · {{ $announcement->ends_at ? 'until '.$announcement->ends_at->toDayDateTimeString() : 'no end date' }}
                    · by {{ $announcement->creator?->name ?? 'unknown' }}
                </p>
            </div>
            <div class="flex shrink-0 flex-col items-end gap-2">
                <x-admin.badge :color="match ($announcement->stateLabel()) { 'Live' => 'green', 'Scheduled' => 'violet', default => 'gray' }">{{ $announcement->stateLabel() }}</x-admin.badge>
                @can(\App\Enums\PermissionArea::WebsiteContent->permission(\App\Enums\PermissionAction::Edit))
                    <a href="{{ route('admin.cms.announcements.edit', $announcement) }}" class="text-sm font-semibold text-brand-600 hover:text-brand-800">Edit</a>
                @endcan
            </div>
        </article>
    @empty
        <p class="rounded-2xl border-2 border-dashed border-slate-200 px-6 py-16 text-center text-sm text-slate-500">No announcements yet.</p>
    @endforelse
</div>
<div class="mt-4">{{ $announcements->links() }}</div>
@endsection
