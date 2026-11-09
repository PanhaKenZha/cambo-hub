@extends('layouts.admin')
@section('title', 'Appeals')
@section('content')
<div>
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Appeals</h1>
    <p class="mt-1 text-sm text-slate-500">Users asking for a rejected submission or a violation to be reconsidered. Decided by a different moderator.</p>
</div>

<div class="mt-6">
    @include('admin.partials.status-tabs', [
        'tabs' => collect(\App\Enums\AppealStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])->put('all', 'All')->all(),
        'routeName' => 'admin.appeals.index',
    ])
</div>

<div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold tracking-wide text-slate-500 uppercase">
                    <th class="px-6 py-3">Appeal</th>
                    <th class="px-6 py-3">From</th>
                    <th class="px-6 py-3">Filed</th>
                    <th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($appeals as $appeal)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.appeals.show', $appeal) }}" class="font-medium text-slate-900 hover:text-brand-700">{{ \Illuminate\Support\Str::limit($appeal->subjectLabel(), 90) }}</a>
                            <p class="text-xs text-slate-500">#{{ $appeal->id }}</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-600">{{ $appeal->user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-500">{{ $appeal->created_at->diffForHumans() }}</td>
                        <td class="px-6 py-4"><x-admin.badge :color="$appeal->status->color()">{{ $appeal->status->label() }}</x-admin.badge></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-16 text-center text-slate-500">{{ $activeStatus === 'pending' ? 'No appeals waiting for a decision.' : 'No appeals here.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($appeals->hasPages())
        <div class="border-t border-slate-200 px-6 py-3">{{ $appeals->links() }}</div>
    @endif
</div>
@endsection
