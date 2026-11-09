@extends('layouts.admin')
@section('title', 'Approvals')
@section('content')
<div>
    <h1 class="text-2xl font-bold tracking-tight text-ink">Approval center</h1>
    <p class="mt-1 text-sm text-ink-muted">Submissions from every module that need a moderator's decision. Oldest first.</p>
</div>

<div class="mt-6">
    @include('admin.partials.status-tabs', [
        'tabs' => collect(\App\Enums\ApprovalStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])->put('all', 'All')->all(),
        'routeName' => 'admin.approvals.index',
    ])
</div>

<form method="GET" action="{{ route('admin.approvals.index') }}" role="search" aria-label="Filter submissions" data-loading
      class="mt-4 grid gap-3 rounded-2xl bg-surface p-4 shadow-card ring-1 ring-line sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_auto] lg:items-end">
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

@if($approvalRequests->isEmpty())
    <x-ui.empty-state class="mt-4" icon="check-circle"
        :title="$activeStatus === 'pending' ? 'All caught up' : 'Nothing here'"
        :description="$activeStatus === 'pending' ? 'There are no submissions waiting for review.' : 'No submissions match these filters.'" />
@else
    <x-ui.table label="Submissions" class="mt-4">
        <x-slot:head>
            <tr>
                <th scope="col">Submission</th>
                <th scope="col">Module</th>
                <th scope="col">Submitted</th>
                <th scope="col">Reviewer</th>
                <th scope="col">Status</th>
            </tr>
        </x-slot:head>
        @foreach($approvalRequests as $approvalRequest)
            <tr class="hover:bg-surface-muted/60">
                <td data-label="Submission">
                    <div class="min-w-0">
                        <a href="{{ route('admin.approvals.show', $approvalRequest) }}" class="rounded font-medium text-ink hover:text-primary">{{ $approvalRequest->title }}</a>
                        <p class="text-xs text-ink-muted">#{{ $approvalRequest->id }} · by {{ $approvalRequest->submitter?->name ?? 'Deleted user' }}</p>
                    </div>
                </td>
                <td data-label="Module"><x-admin.badge>{{ $approvalRequest->module->label() }}</x-admin.badge></td>
                <td data-label="Submitted" class="whitespace-nowrap" title="{{ $approvalRequest->created_at->toDayDateTimeString() }}">{{ $approvalRequest->created_at->diffForHumans() }}</td>
                <td data-label="Reviewer" class="whitespace-nowrap text-ink">{{ $approvalRequest->assignee?->name ?? '—' }}</td>
                <td data-label="Status"><x-admin.badge :color="$approvalRequest->status->color()">{{ $approvalRequest->status->label() }}</x-admin.badge></td>
            </tr>
        @endforeach
    </x-ui.table>
@endif

@if($approvalRequests->hasPages())
    <div class="mt-4">{{ $approvalRequests->links() }}</div>
@endif
@endsection
