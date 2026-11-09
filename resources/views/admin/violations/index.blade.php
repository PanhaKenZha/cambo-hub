@extends('layouts.admin')
@section('title', 'Violations')
@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">User violations</h1>
        <p class="mt-1 text-sm text-slate-500">Warnings, strikes, suspensions and bans. {{ $activeCount }} currently active.</p>
    </div>
    @can('create', \App\Models\UserViolation::class)
        <x-admin.button :href="route('admin.violations.create')">Issue violation</x-admin.button>
    @endcan
</div>

<form method="GET" action="{{ route('admin.violations.index') }}" class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_auto] lg:items-end">
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

<div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold tracking-wide text-slate-500 uppercase">
                    <th class="px-6 py-3">User</th>
                    <th class="px-6 py-3">Penalty</th>
                    <th class="px-6 py-3">Reason</th>
                    <th class="px-6 py-3">Issued</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($violations as $violation)
                    <tr class="align-top hover:bg-slate-50/70">
                        <td class="px-6 py-4">
                            <p class="font-medium text-slate-900">{{ $violation->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $violation->user->email }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <x-admin.badge :color="$violation->type->color()">{{ $violation->type->label() }}</x-admin.badge>
                            @if($violation->expires_at)<p class="mt-1 text-xs text-slate-500">until {{ $violation->expires_at->toFormattedDateString() }}</p>@endif
                        </td>
                        <td class="max-w-sm px-6 py-4 text-slate-700">{{ $violation->reason }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-500">{{ $violation->created_at->diffForHumans() }}<br><span class="text-xs">by {{ $violation->issuer?->name ?? 'unknown' }}</span></td>
                        <td class="px-6 py-4"><x-admin.badge :color="$violation->status->color()">{{ $violation->status->label() }}</x-admin.badge></td>
                        <td class="px-6 py-4 text-right">
                            @can('overturn', $violation)
                                <form method="POST" action="{{ route('admin.violations.overturn', $violation) }}" onsubmit="return confirm('Overturn this {{ strtolower($violation->type->label()) }}? Suspensions and bans are lifted immediately.')">
                                    @csrf
                                    <button type="submit" class="cursor-pointer text-sm font-semibold text-brand-600 hover:text-brand-800">Overturn</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-16 text-center text-slate-500">No violations match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($violations->hasPages())
        <div class="border-t border-slate-200 px-6 py-3">{{ $violations->links() }}</div>
    @endif
</div>
@endsection
