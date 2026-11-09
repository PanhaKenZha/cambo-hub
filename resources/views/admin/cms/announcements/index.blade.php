@extends('layouts.admin')
@section('title', 'Announcements')
@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-ink">Announcements</h1>
        <p class="mt-1 text-sm text-ink-muted">Notices shown at the top of the public website, e.g. holidays, new features or service updates.</p>
    </div>
    @can(\App\Enums\PermissionArea::WebsiteContent->permission(\App\Enums\PermissionAction::Create))
        <x-admin.button :href="route('admin.cms.announcements.create')">New announcement</x-admin.button>
    @endcan
</div>

<div class="mt-6 space-y-3">
    @forelse($announcements as $announcement)
        <article class="flex flex-wrap items-start gap-x-4 gap-y-3 rounded-2xl bg-surface p-5 shadow-card ring-1 ring-line sm:flex-nowrap">
            <span @class(['mt-1.5 size-2.5 shrink-0 rounded-full', 'bg-primary' => $announcement->level->value === 'info', 'bg-success' => $announcement->level->value === 'success', 'bg-warning' => $announcement->level->value === 'warning']) aria-hidden="true"></span>
            <div class="min-w-0 flex-1">
                <p class="text-sm break-words text-ink">{{ $announcement->translate('message', 'en') }}</p>
                @if(filled($announcement->message['km'] ?? null))<p class="mt-0.5 text-sm break-words text-ink-muted">{{ $announcement->message['km'] }}</p>@endif
                <p class="mt-2 text-xs text-ink-subtle">
                    {{ $announcement->starts_at ? 'From '.$announcement->starts_at->toDayDateTimeString() : 'Starts immediately' }}
                    · {{ $announcement->ends_at ? 'until '.$announcement->ends_at->toDayDateTimeString() : 'no end date' }}
                    · by {{ $announcement->creator?->name ?? 'unknown' }}
                </p>
            </div>
            <div class="flex w-full shrink-0 flex-row items-center gap-3 pl-6.5 sm:w-auto sm:flex-col sm:items-end sm:gap-2 sm:pl-0">
                <x-admin.badge :color="match ($announcement->stateLabel()) { 'Live' => 'green', 'Scheduled' => 'violet', default => 'gray' }">{{ $announcement->stateLabel() }}</x-admin.badge>
                @can(\App\Enums\PermissionArea::WebsiteContent->permission(\App\Enums\PermissionAction::Edit))
                    <a href="{{ route('admin.cms.announcements.edit', $announcement) }}" class="rounded text-sm font-semibold text-primary hover:text-primary-hover">Edit</a>
                @endcan
            </div>
        </article>
    @empty
        <x-ui.empty-state icon="notification" title="No announcements yet." />
    @endforelse
</div>
<div class="mt-4">{{ $announcements->links() }}</div>
@endsection
