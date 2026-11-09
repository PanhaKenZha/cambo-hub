@props(['variant' => 'info', 'title' => null])
@php
    $styles = match ($variant) {
        'success' => ['box' => 'border-success/30 bg-success-soft text-success', 'icon' => 'M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.86-9.81a.75.75 0 0 0-1.22-.88l-3.24 4.48-1.6-1.6a.75.75 0 1 0-1.06 1.06l2.22 2.22a.75.75 0 0 0 1.14-.09l3.76-5.2Z'],
        'error' => ['box' => 'border-danger/30 bg-danger-soft text-danger', 'icon' => 'M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z'],
        'warning' => ['box' => 'border-warning/30 bg-warning-soft text-warning', 'icon' => 'M8.49 2.87a1.75 1.75 0 0 1 3.02 0l6.28 10.88A1.75 1.75 0 0 1 16.28 16.4H3.72a1.75 1.75 0 0 1-1.51-2.63L8.49 2.87ZM10 7a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 10 7Zm0 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z'],
        default => ['box' => 'border-primary/25 bg-primary-soft text-primary', 'icon' => 'M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.25v2.75a.75.75 0 0 0 1.5 0V9.75A.75.75 0 0 0 10 9H9Z'],
    };
@endphp
<div role="{{ $variant === 'error' ? 'alert' : 'status' }}" {{ $attributes->class(['flex gap-3 rounded-xl border px-4 py-3 text-sm', $styles['box']]) }}>
    <svg class="mt-0.5 size-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="{{ $styles['icon'] }}"/></svg>
    <div class="min-w-0 space-y-1">
        @if($title)<p class="font-semibold">{{ $title }}</p>@endif
        <div class="text-ink-muted">{{ $slot }}</div>
    </div>
</div>
