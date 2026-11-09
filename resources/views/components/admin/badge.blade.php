@props(['color' => 'blue'])

<span {{ $attributes->class([
    'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset whitespace-nowrap',
    match ($color) {
        'green' => 'bg-success-soft text-success ring-success/25',
        'red' => 'bg-danger-soft text-danger ring-danger/25',
        'amber' => 'bg-warning-soft text-warning ring-warning/25',
        'gray' => 'bg-surface-muted text-ink-muted ring-line-strong',
        'violet' => 'bg-violet-50 text-violet-700 ring-violet-600/20 dark:bg-violet-400/10 dark:text-violet-300 dark:ring-violet-400/25',
        default => 'bg-primary-soft text-primary ring-primary/20',
    },
]) }}>{{ $slot }}</span>
