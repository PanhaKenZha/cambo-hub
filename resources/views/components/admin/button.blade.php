@props(['variant' => 'primary', 'href' => null, 'loadingText' => null])

@php
    $classes = 'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-50 aria-disabled:cursor-progress aria-disabled:opacity-80 cursor-pointer '.match ($variant) {
        'secondary' => 'bg-surface text-ink ring-1 ring-inset ring-line-strong hover:bg-surface-muted hover:text-primary focus-visible:outline-brand-500 dark:focus-visible:outline-brand-300',
        'danger' => 'bg-red-600 text-white shadow-sm hover:bg-red-700 focus-visible:outline-red-600 dark:focus-visible:outline-red-400',
        'ghost' => 'text-ink-muted hover:bg-surface-muted hover:text-primary focus-visible:outline-brand-500 dark:focus-visible:outline-brand-300',
        default => 'bg-primary text-on-primary shadow-sm hover:bg-primary-hover focus-visible:outline-brand-700 dark:focus-visible:outline-brand-300',
    };
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }} @if($loadingText) data-loading-text="{{ $loadingText }}" @endif>
        <svg data-loading-indicator hidden class="size-4 shrink-0 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".25" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
        <span data-loading-label class="inline-flex items-center gap-2">{{ $slot }}</span>
    </button>
@endif
