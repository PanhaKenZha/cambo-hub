@extends('layouts.admin')
@section('title', $activeGroup === 'modules' ? 'Modules' : 'Platform settings')
@section('content')
<div class="flex flex-col gap-1">
    <h1 class="text-2xl font-bold tracking-tight text-ink">{{ $activeGroup === 'modules' ? 'Modules' : 'Platform settings' }}</h1>
    <p class="text-sm text-ink-muted">Control the platform without changing code. Changes take effect as soon as you save.</p>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-[15rem_1fr]">
    {{-- Tabs --}}
    <nav class="-mx-4 flex gap-1 overflow-x-auto px-4 pb-1 lg:mx-0 lg:flex-col lg:overflow-visible lg:px-0" aria-label="Settings sections">
        @foreach($groups as $groupKey => $group)
            @php($isActive = $groupKey === $activeGroup)
            <a href="{{ route('admin.settings.edit', $groupKey) }}" @if($isActive) aria-current="page" @endif
               @class([
                   'flex shrink-0 items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap transition',
                   'bg-surface text-primary shadow-card ring-1 ring-line' => $isActive,
                   'text-ink-muted hover:bg-surface-muted hover:text-ink' => ! $isActive,
               ])>
                <svg @class(['size-5 shrink-0', 'text-primary' => $isActive, 'text-ink-subtle' => ! $isActive]) fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $group['icon'] }}"/></svg>
                {{ $group['label'] }}
            </a>
        @endforeach
    </nav>

    {{-- Active tab --}}
    <form method="POST" action="{{ route('admin.settings.update', $activeGroup) }}" class="min-w-0" data-loading>
        <x-ui.error-summary class="mb-6" :ids="collect($errors->keys())->mapWithKeys(fn ($key) => [$key => preg_replace('/\.\d+$/', '', $key)])->all()" />
        @csrf @method('PUT')

        <section class="overflow-hidden rounded-2xl bg-surface shadow-card ring-1 ring-line">
            <header class="flex flex-col gap-2 border-b border-line px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-ink">{{ $definition['label'] }}</h2>
                    <p class="mt-0.5 text-sm text-ink-muted">{{ $definition['description'] }}</p>
                </div>
                <div class="flex shrink-0 flex-col items-start gap-1 sm:items-end">
                    @unless($canEdit)
                        <x-admin.badge color="amber">View only</x-admin.badge>
                    @endunless
                    @if($lastChange)
                        <p class="text-xs text-ink-subtle">Last changed {{ $lastChange->updated_at->diffForHumans() }}{{ $lastChange->updatedBy ? ' by '.$lastChange->updatedBy->name : '' }}</p>
                    @endif
                </div>
            </header>

            @if($activeGroup === 'modules')
                <div class="grid gap-4 p-6 sm:grid-cols-2 2xl:grid-cols-3">
                    @foreach($definition['fields'] as $name => $field)
                        <div class="rounded-xl p-4 ring-1 ring-line has-checked:bg-primary-soft/50 has-checked:ring-primary/40">
                            @include('admin.settings._field', ['name' => $name, 'field' => $field, 'value' => $values[$name]])
                        </div>
                    @endforeach
                </div>
            @else
                <div class="space-y-6 p-6">
                    @foreach($definition['fields'] as $name => $field)
                        <div @class(['max-w-2xl' => $field['type'] !== 'boolean'])>
                            @include('admin.settings._field', ['name' => $name, 'field' => $field, 'value' => $values[$name]])
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        @if($canEdit)
            <div class="sticky bottom-0 mt-6 -mx-4 flex flex-wrap justify-end gap-3 border-t border-line bg-surface/95 px-4 py-4 backdrop-blur sm:-mx-6 sm:px-6 lg:mx-0 lg:rounded-2xl lg:border lg:px-6">
                <x-admin.button variant="secondary" :href="route('admin.settings.edit', $activeGroup)">Discard changes</x-admin.button>
                <x-admin.button loading-text="Saving…">Save {{ \Illuminate\Support\Str::lower($definition['label']) }}</x-admin.button>
            </div>
        @endif
    </form>
</div>
@endsection
