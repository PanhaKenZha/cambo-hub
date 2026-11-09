@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'type' => 'submit', 'loadingText' => null])
@php
    $classes = [
        'inline-flex items-center justify-center gap-2 rounded-xl font-semibold whitespace-nowrap transition duration-150 select-none active:translate-y-px focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-brand-500 disabled:cursor-not-allowed disabled:opacity-60 aria-disabled:cursor-progress aria-disabled:opacity-80',
        match ($size) {
            'sm' => 'h-9 px-3.5 text-xs',
            'lg' => 'h-12 px-6 text-base',
            default => 'h-11 px-5 text-sm',
        },
        match ($variant) {
            'secondary' => 'border border-line-strong bg-surface text-ink hover:border-primary hover:text-primary',
            'ghost' => 'text-primary hover:bg-primary-soft',
            'danger' => 'bg-red-600 text-white shadow-sm hover:bg-red-700',
            'white' => 'bg-white text-brand-700 shadow-sm hover:bg-brand-50',
            default => 'bg-primary text-on-primary shadow-sm hover:bg-primary-hover',
        },
    ];
@endphp
@if($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" @if($loadingText) data-loading-text="{{ $loadingText }}" @endif {{ $attributes->class($classes) }}>
        <svg data-loading-indicator hidden class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".25" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
        <span data-loading-label class="inline-flex items-center gap-2">{{ $slot }}</span>
    </button>
@endif
