@props(['variant' => 'primary', 'href' => null])

@php
    $classes = 'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-50 cursor-pointer '.match ($variant) {
        'secondary' => 'bg-white text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 focus-visible:outline-brand-500',
        'danger' => 'bg-red-600 text-white shadow-sm hover:bg-red-700 focus-visible:outline-red-600',
        'ghost' => 'text-slate-600 hover:text-brand-700 hover:bg-slate-100 focus-visible:outline-brand-500',
        default => 'bg-brand-700 text-white shadow-sm hover:bg-brand-800 focus-visible:outline-brand-700',
    };
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>{{ $slot }}</button>
@endif
