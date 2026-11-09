@props(['variant' => 'primary'])
<span {{ $attributes->class([
    'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold whitespace-nowrap',
    match ($variant) {
        'accent' => 'bg-accent-soft text-accent',
        'success' => 'bg-success-soft text-success',
        'warning' => 'bg-warning-soft text-warning',
        'danger' => 'bg-danger-soft text-danger',
        'neutral' => 'bg-surface-muted text-ink-muted',
        'solid' => 'bg-cambo-red text-white',
        default => 'bg-primary-soft text-primary',
    },
]) }}>{{ $slot }}</span>
