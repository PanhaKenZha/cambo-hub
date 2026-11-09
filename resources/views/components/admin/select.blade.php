@props(['name', 'label', 'errorBag' => 'default', 'hint' => null])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->getBag($errorBag)->first($name) ?: null;
    $describedBy = trim(($hint ? $id.'-hint ' : '').($error ? $id.'-error' : ''));
@endphp

<div>
    <label for="{{ $id }}" class="block text-sm font-medium text-ink">
        {{ $label }}@if($attributes->has('required'))<span class="ml-0.5 text-accent" aria-hidden="true">*</span>@endif
    </label>
    <div class="relative mt-1.5">
        <select
            id="{{ $id }}"
            name="{{ $name }}"
            @if($error) aria-invalid="true" @endif
            @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->except('id')->class('form-control appearance-none rounded-lg pr-10 shadow-sm') }}
        >{{ $slot }}</select>
        <svg class="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-ink-subtle" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
    </div>
    @if($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-xs text-ink-subtle">{{ $hint }}</p>
    @endif
    @if($error)
        <p id="{{ $id }}-error" class="mt-1.5 flex items-start gap-1.5 text-sm font-medium text-danger" role="alert">
            <svg class="mt-0.5 size-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
            <span>{{ $error }}</span>
        </p>
    @endif
</div>
