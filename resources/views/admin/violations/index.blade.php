@extends('layouts.admin')
@section('title', 'Violations')
@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-ink">User violations</h1>
        <p class="mt-1 text-sm text-ink-muted">Warnings, strikes, suspensions and bans. {{ $activeCount }} currently active.</p>
    </div>
    @can('create', \App\Models\UserViolation::class)
        <x-admin.button :href="route('admin.violations.create')">Issue violation</x-admin.button>
    @endcan
</div>

<form method="GET" action="{{ route('admin.violations.index') }}" role="search" aria-label="Filter violations" data-loading
      class="mt-6 grid gap-3 rounded-2xl bg-surface p-4 shadow-card ring-1 ring-line sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_auto] lg:items-end">
    <x-admin.input name="search" label="User" :value="$filters['search'] ?? ''" placeholder="Name or email" />
    <x-admin.select name="type" label="Type">
        <option value="">Any type</option>
        @foreach(\App\Enums\ViolationType::cases() as $type)
            <option value="{{ $type->value }}" @selected(($filters['type'] ?? '') === $type->value)>{{ $type->label() }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.select name="status" label="Status">
        <option value="">Any status</option>
        @foreach(\App\Enums\ViolationStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.button variant="secondary">Filter</x-admin.button>
</form>

@if($violations->isEmpty())
    <x-ui.empty-state class="mt-4" title="No violations match these filters." />
@else
    <x-ui.table label="User violations" class="mt-4">
        <x-slot:head>
            <tr>
                <th scope="col">User</th>
                <th scope="col">Penalty</th>
                <th scope="col">Reason</th>
                <th scope="col">Issued</th>
                <th scope="col">Status</th>
                <th scope="col"><span class="sr-only">Actions</span></th>
            </tr>
        </x-slot:head>
        @foreach($violations as $violation)
            <tr class="align-top hover:bg-surface-muted/60">
                <td data-label="User">
                    <div class="min-w-0">
                        <p class="font-medium text-ink">{{ $violation->user->name }}</p>
                        <p class="text-xs break-all text-ink-muted">{{ $violation->user->email }}</p>
                    </div>
                </td>
                <td data-label="Penalty">
                    <div>
                        <x-admin.badge :color="$violation->type->color()">{{ $violation->type->label() }}</x-admin.badge>
                        @if($violation->expires_at)<p class="mt-1 text-xs text-ink-muted">until {{ $violation->expires_at->toFormattedDateString() }}</p>@endif
                    </div>
                </td>
                <td data-label="Reason" class="text-ink sm:max-w-sm">{{ $violation->reason }}</td>
                <td data-label="Issued" class="whitespace-nowrap">
                    <div>{{ $violation->created_at->diffForHumans() }}<br><span class="text-xs text-ink-subtle">by {{ $violation->issuer?->name ?? 'unknown' }}</span></div>
                </td>
                <td data-label="Status"><x-admin.badge :color="$violation->status->color()">{{ $violation->status->label() }}</x-admin.badge></td>
                <td data-label="Actions" class="sm:text-right">
                    <div>
                        @can('overturn', $violation)
                            <form method="POST" action="{{ route('admin.violations.overturn', $violation) }}" onsubmit="return confirm('Overturn this {{ strtolower($violation->type->label()) }}? Suspensions and bans are lifted immediately.')">
                                @csrf
                                <button type="submit" class="cursor-pointer rounded text-sm font-semibold text-primary hover:text-primary-hover">Overturn</button>
                            </form>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
    </x-ui.table>
@endif

@if($violations->hasPages())
    <div class="mt-4">{{ $violations->links() }}</div>
@endif
@endsection
