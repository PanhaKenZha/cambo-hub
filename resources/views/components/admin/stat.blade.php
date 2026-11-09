@props(['label', 'value', 'icon', 'tone' => 'brand', 'hint' => null, 'href' => null])

@php
    $toneClasses = match ($tone) {
        'green' => 'bg-success-soft text-success',
        'amber' => 'bg-warning-soft text-warning',
        'red' => 'bg-danger-soft text-danger',
        'violet' => 'bg-violet-50 text-violet-600 dark:bg-violet-400/10 dark:text-violet-300',
        default => 'bg-primary-soft text-primary',
    };
@endphp

<div {{ $attributes->class('group relative rounded-2xl bg-surface p-5 shadow-card ring-1 ring-line transition hover:shadow-card-hover') }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-sm font-medium text-ink-muted">{{ $label }}</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-ink tabular-nums">{{ is_numeric($value) ? number_format($value) : $value }}</p>
        </div>
        <span class="flex size-11 shrink-0 items-center justify-center rounded-xl {{ $toneClasses }}">
            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
        </span>
    </div>
    @if($hint)
        <p class="mt-3 text-xs text-ink-subtle">{{ $hint }}</p>
    @endif
    @if($href)
        <a href="{{ $href }}" class="absolute inset-0 rounded-2xl" aria-label="{{ $label }}"></a>
    @endif
</div>
