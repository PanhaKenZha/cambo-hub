@props(['name', 'label', 'type' => 'text', 'value' => null, 'errorBag' => 'default', 'hint' => null])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->getBag($errorBag)->first($name) ?: null;
    $describedBy = trim(($hint ? $id.'-hint ' : '').($error ? $id.'-error' : ''));
@endphp

<div>
    <label for="{{ $id }}" class="block text-sm font-medium text-ink">
        {{ $label }}@if($attributes->has('required'))<span class="ml-0.5 text-accent" aria-hidden="true">*</span>@endif
    </label>
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if($type !== 'password') value="{{ old($name, $value) }}" @endif
        @if($error) aria-invalid="true" @endif
        @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except('id')->class('form-control mt-1.5 rounded-lg shadow-sm') }}
    >
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
