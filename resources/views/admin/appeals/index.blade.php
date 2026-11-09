@extends('layouts.admin')
@section('title', 'Appeals')
@section('content')
<div>
    <h1 class="text-2xl font-bold tracking-tight text-ink">Appeals</h1>
    <p class="mt-1 text-sm text-ink-muted">Users asking for a rejected submission or a violation to be reconsidered. Decided by a different moderator.</p>
</div>

<div class="mt-6">
    @include('admin.partials.status-tabs', [
        'tabs' => collect(\App\Enums\AppealStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])->put('all', 'All')->all(),
        'routeName' => 'admin.appeals.index',
    ])
</div>

@if($appeals->isEmpty())
    <x-ui.empty-state class="mt-4" :title="$activeStatus === 'pending' ? 'No appeals waiting for a decision.' : 'No appeals here.'" />
@else
    <x-ui.table label="Appeals" class="mt-4">
        <x-slot:head>
            <tr>
                <th scope="col">Appeal</th>
                <th scope="col">From</th>
                <th scope="col">Filed</th>
                <th scope="col">Status</th>
            </tr>
        </x-slot:head>
        @foreach($appeals as $appeal)
            <tr class="hover:bg-surface-muted/60">
                <td data-label="Appeal">
                    <div class="min-w-0">
                        <a href="{{ route('admin.appeals.show', $appeal) }}" class="rounded font-medium text-ink hover:text-primary">{{ \Illuminate\Support\Str::limit($appeal->subjectLabel(), 90) }}</a>
                        <p class="text-xs text-ink-muted">#{{ $appeal->id }}</p>
                    </div>
                </td>
                <td data-label="From" class="whitespace-nowrap text-ink">{{ $appeal->user->name }}</td>
                <td data-label="Filed" class="whitespace-nowrap">{{ $appeal->created_at->diffForHumans() }}</td>
                <td data-label="Status"><x-admin.badge :color="$appeal->status->color()">{{ $appeal->status->label() }}</x-admin.badge></td>
            </tr>
        @endforeach
    </x-ui.table>
@endif

@if($appeals->hasPages())
    <div class="mt-4">{{ $appeals->links() }}</div>
@endif
@endsection
