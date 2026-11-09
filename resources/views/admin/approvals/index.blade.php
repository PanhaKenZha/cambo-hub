@extends('layouts.admin')
@section('title', 'Approvals')
@section('content')
<div>
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Approval center</h1>
    <p class="mt-1 text-sm text-slate-500">Submissions from every module that need a moderator's decision. Oldest first.</p>
</div>

<div class="mt-6">
    @include('admin.partials.status-tabs', [
        'tabs' => collect(\App\Enums\ApprovalStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])->put('all', 'All')->all(),
        'routeName' => 'admin.approvals.index',
    ])
</div>

<form method="GET" action="{{ route('admin.approvals.index') }}" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_auto] lg:items-end">
    <input type="hidden" name="status" value="{{ $activeStatus }}">
    <x-admin.input name="search" label="Search" :value="$filters['search'] ?? ''" placeholder="Title" />
    <x-admin.select name="module" label="Module">
        <option value="">All modules</option>
        @foreach($modules as $module)
            <option value="{{ $module->value }}" @selected(($filters['module'] ?? '') === $module->value)>{{ $module->label() }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.select name="assigned" label="Reviewer">
        <option value="">Anyone</option>
        <option value="me" @selected(($filters['assigned'] ?? '') === 'me')>Assigned to me</option>
        <option value="unassigned" @selected(($filters['assigned'] ?? '') === 'unassigned')>Unassigned</option>
    </x-admin.select>
    <x-admin.button variant="secondary">Filter</x-admin.button>
</form>

<div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold tracking-wide text-slate-500 uppercase">
                    <th class="px-6 py-3">Submission</th>
                    <th class="px-6 py-3">Module</th>
                    <th class="px-6 py-3">Submitted</th>
                    <th class="px-6 py-3">Reviewer</th>
                    <th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($approvalRequests as $approvalRequest)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.approvals.show', $approvalRequest) }}" class="font-medium text-slate-900 hover:text-brand-700">{{ $approvalRequest->title }}</a>
                            <p class="text-xs text-slate-500">#{{ $approvalRequest->id }} · by {{ $approvalRequest->submitter?->name ?? 'Deleted user' }}</p>
                        </td>
                        <td class="px-6 py-4"><x-admin.badge>{{ $approvalRequest->module->label() }}</x-admin.badge></td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-500" title="{{ $approvalRequest->created_at->toDayDateTimeString() }}">{{ $approvalRequest->created_at->diffForHumans() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $approvalRequest->assignee?->name ?? '—' }}</td>
                        <td class="px-6 py-4"><x-admin.badge :color="$approvalRequest->status->color()">{{ $approvalRequest->status->label() }}</x-admin.badge></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <svg class="mx-auto size-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="mt-3 font-medium text-slate-700">{{ $activeStatus === 'pending' ? 'All caught up' : 'Nothing here' }}</p>
                            <p class="mt-1 text-slate-500">{{ $activeStatus === 'pending' ? 'There are no submissions waiting for review.' : 'No submissions match these filters.' }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($approvalRequests->hasPages())
        <div class="border-t border-slate-200 px-6 py-3">{{ $approvalRequests->links() }}</div>
    @endif
</div>
@endsection
