@props(['level'])

@if($level === 'none')
    <span {{ $attributes->class('text-slate-300') }} aria-label="No access">—</span>
@else
    <span {{ $attributes->class([
        'inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset whitespace-nowrap',
        'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $level === 'full',
        'bg-brand-50 text-brand-700 ring-brand-600/20' => $level === 'manage',
        'bg-slate-100 text-slate-600 ring-slate-500/20' => $level === 'view',
    ]) }}>{{ ucfirst($level) }}</span>
@endif
