@extends('layouts.admin')
@section('title', 'Companies')
@section('content')
@php
    use App\Enums\VerificationStatus;

    $verificationColors = [
        VerificationStatus::Verified->value => 'green',
        VerificationStatus::Pending->value => 'amber',
        VerificationStatus::Rejected->value => 'red',
        VerificationStatus::Unverified->value => 'gray',
    ];
@endphp
@include('admin.jobs._nav')

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-ink">Companies</h1>
        <p class="mt-1 text-sm text-ink-muted">Employers that post jobs. Verified companies get a ✓ badge and, if enabled in Settings, publish without review.</p>
    </div>
    @can(\App\Enums\PermissionArea::Jobs->permission(\App\Enums\PermissionAction::Create))
        <x-admin.button :href="route('admin.jobs.companies.create')">Add company</x-admin.button>
    @endcan
</div>

<x-ui.error-summary class="mt-4" />

<form method="GET" action="{{ route('admin.jobs.companies.index') }}" role="search" aria-label="Filter companies" data-loading
      class="mt-6 grid gap-3 rounded-2xl bg-surface p-4 shadow-card ring-1 ring-line sm:grid-cols-[2fr_1fr_auto] sm:items-end">
    <x-admin.input name="search" label="Search" :value="$filters['search'] ?? ''" placeholder="Company name or email" />
    <x-admin.select name="verification" label="Verification">
        <option value="">All</option>
        @foreach(VerificationStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected(($filters['verification'] ?? null) === $status->value)>{{ Str::headline($status->value) }} ({{ $statusCounts[$status->value] ?? 0 }})</option>
        @endforeach
    </x-admin.select>
    <x-admin.button variant="secondary">Filter</x-admin.button>
</form>

@if($companies->isEmpty())
    <x-ui.empty-state class="mt-4" title="No companies found." />
@else
    <x-ui.table label="Companies" class="mt-4">
        <x-slot:head>
            <tr>
                <th scope="col">Company</th>
                <th scope="col">Contact</th>
                <th scope="col">Owner account</th>
                <th scope="col" class="text-right">Jobs</th>
                <th scope="col">Verification</th>
                <th scope="col"><span class="sr-only">Actions</span></th>
            </tr>
        </x-slot:head>
        @foreach($companies as $company)
            <tr class="hover:bg-surface-muted/60">
                <td data-label="Company">
                    <div class="flex items-center gap-3">
                        @if($company->logoUrl())
                            <img src="{{ $company->logoUrl() }}" alt="" class="size-9 shrink-0 rounded-lg object-cover ring-1 ring-line">
                        @else
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary-soft text-sm font-bold text-primary">{{ Str::upper(Str::substr($company->name, 0, 1)) }}</span>
                        @endif
                        <div class="min-w-0">
                            <a href="{{ route('admin.jobs.companies.edit', $company) }}" class="rounded font-medium text-ink hover:text-primary">{{ $company->name }}</a>
                            <p class="text-xs text-ink-subtle">{{ $company->city ?? '—' }}@unless($company->is_active) · <span class="text-danger">inactive</span>@endunless</p>
                        </div>
                    </div>
                </td>
                <td data-label="Contact" class="text-sm">
                    <div>{{ $company->email ?? '—' }}</div>
                    @if($company->phone)<div class="text-xs text-ink-subtle">{{ $company->phone }}</div>@endif
                </td>
                <td data-label="Owner account" class="text-sm">{{ $company->owner?->email ?? '—' }}</td>
                <td data-label="Jobs" class="text-right tabular-nums">
                    @if($company->job_listings_count > 0)
                        <a href="{{ route('admin.jobs.listings.index', ['company' => $company->id]) }}" class="text-primary hover:underline">{{ $company->job_listings_count }}</a>
                    @else
                        0
                    @endif
                </td>
                <td data-label="Verification">
                    <x-admin.badge :color="$verificationColors[$company->verification_status->value]">
                        {{ $company->isVerified() ? '✓ ' : '' }}{{ Str::headline($company->verification_status->value) }}
                    </x-admin.badge>
                </td>
                <td class="text-right whitespace-nowrap">
                    @can(\App\Enums\PermissionArea::Jobs->permission(\App\Enums\PermissionAction::Edit))
                        <a href="{{ route('admin.jobs.companies.edit', $company) }}" class="rounded text-sm font-semibold text-primary hover:text-primary-hover">
                            {{ $company->verification_status === VerificationStatus::Pending ? 'Review' : 'Edit' }}
                        </a>
                    @endcan
                </td>
            </tr>
        @endforeach
    </x-ui.table>
    @if($companies->hasPages())<div class="mt-4">{{ $companies->links() }}</div>@endif
@endif
@endsection
