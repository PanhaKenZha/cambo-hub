@props(['label', 'value', 'icon', 'tone' => 'brand', 'hint' => null, 'href' => null])

@php
    $toneClasses = match ($tone) {
        'green' => 'bg-emerald-50 text-emerald-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'red' => 'bg-red-50 text-red-600',
        'violet' => 'bg-violet-50 text-violet-600',
        default => 'bg-brand-50 text-brand-600',
    };
@endphp

<div {{ $attributes->class('group relative rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 transition hover:shadow-md') }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 tabular-nums">{{ is_numeric($value) ? number_format($value) : $value }}</p>
        </div>
        <span class="flex size-11 shrink-0 items-center justify-center rounded-xl {{ $toneClasses }}">
            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
        </span>
    </div>
    @if($hint)
        <p class="mt-3 text-xs text-slate-500">{{ $hint }}</p>
    @endif
    @if($href)
        <a href="{{ $href }}" class="absolute inset-0 rounded-2xl" aria-label="{{ $label }}"></a>
    @endif
</div>
