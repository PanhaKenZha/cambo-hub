@props(['level'])

@if($level === 'none')
    <span {{ $attributes->class('text-ink-subtle/60') }} aria-label="No access">—</span>
@else
    <span {{ $attributes->class([
        'inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset whitespace-nowrap',
        'bg-success-soft text-success ring-success/25' => $level === 'full',
        'bg-primary-soft text-primary ring-primary/25' => $level === 'manage',
        'bg-surface-muted text-ink-muted ring-line-strong' => $level === 'view',
    ]) }}>{{ ucfirst($level) }}</span>
@endif
