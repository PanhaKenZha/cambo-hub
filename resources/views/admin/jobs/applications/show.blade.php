@extends('layouts.admin')
@section('title', 'Application from '.$application->name)
@section('content')
@include('admin.jobs._nav')

@php($job = $application->jobListing)
<a href="{{ route('admin.jobs.applications.index', ['job' => $job->id]) }}" class="rounded text-sm font-medium text-ink-muted hover:text-primary">&larr; Applications for this job</a>
<div class="mt-3 flex flex-wrap items-center gap-3">
    <h1 class="text-2xl font-bold tracking-tight text-ink">{{ $application->name }}</h1>
    <x-admin.badge :color="$application->status->color()">{{ $application->status->label() }}</x-admin.badge>
</div>
<p class="mt-1 text-sm text-ink-muted">
    Applied for <a href="{{ route('admin.jobs.listings.edit', $job) }}" class="font-semibold text-primary hover:underline">{{ $job->translate('title', 'en') }}</a>
    at {{ $job->company->name }} · {{ $application->created_at->format('j M Y, H:i') }}
</p>

<x-ui.error-summary class="mt-4" />

<div class="mt-6 grid gap-6 xl:grid-cols-[1fr_21rem]">
    <div class="min-w-0 space-y-6">
        <x-admin.card title="Candidate">
            <dl class="grid gap-4 text-sm sm:grid-cols-3">
                <div><dt class="text-ink-subtle">Email</dt><dd class="mt-0.5 font-medium break-all text-ink"><a href="mailto:{{ $application->email }}" class="hover:text-primary">{{ $application->email }}</a></dd></div>
                <div><dt class="text-ink-subtle">Phone</dt><dd class="mt-0.5 font-medium text-ink">{{ $application->phone ?? '—' }}</dd></div>
                <div><dt class="text-ink-subtle">Account</dt><dd class="mt-0.5 font-medium text-ink">{{ $application->user?->email ?? 'Deleted account' }}</dd></div>
            </dl>
            <div class="mt-5 border-t border-line pt-5">
                @if($application->cv_path)
                    <x-admin.button variant="secondary" :href="route('admin.jobs.applications.cv', $application)">
                        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z"/><path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"/></svg>
                        Download CV
                    </x-admin.button>
                    <span class="ml-2 text-xs text-ink-subtle">{{ $application->cv_original_name }}</span>
                @else
                    <p class="text-sm text-ink-muted">No CV attached.</p>
                @endif
            </div>
        </x-admin.card>

        <x-admin.card title="Cover letter">
            @if(filled($application->cover_letter))
                <p class="text-sm leading-relaxed whitespace-pre-line text-ink">{{ $application->cover_letter }}</p>
            @else
                <p class="text-sm text-ink-muted">The candidate did not write a cover letter.</p>
            @endif
        </x-admin.card>
    </div>

    <x-admin.card title="Hiring progress">
        @can(\App\Enums\PermissionArea::Jobs->permission(\App\Enums\PermissionAction::Edit))
            <form method="POST" action="{{ route('admin.jobs.applications.update', $application) }}" class="space-y-4" data-loading>
                @csrf @method('PUT')
                <x-admin.select name="status" label="Status">
                    @foreach(\App\Enums\ApplicationStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $application->status->value) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </x-admin.select>
                <div>
                    <label for="notes" class="block text-sm font-medium text-ink">Internal notes</label>
                    <textarea id="notes" name="notes" rows="5" maxlength="2000" class="form-control mt-1.5 rounded-lg shadow-sm"
                              placeholder="Only admins see these notes.">{{ old('notes', $application->notes) }}</textarea>
                </div>
                <x-admin.button class="w-full" loading-text="Saving…">Save</x-admin.button>
            </form>
        @else
            <p class="text-sm text-ink-muted">{{ $application->notes ?: 'No notes.' }}</p>
        @endcan
        @if($application->reviewer)
            <p class="mt-4 text-xs text-ink-subtle">Last updated by {{ $application->reviewer->name }} · {{ $application->updated_at->diffForHumans() }}</p>
        @endif
    </x-admin.card>
</div>
@endsection
