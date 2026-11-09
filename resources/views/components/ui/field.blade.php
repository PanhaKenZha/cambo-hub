{{-- Label, hint and validation message wrapper shared by every form control. --}}
@props(['id', 'label', 'hint' => null, 'error' => null, 'required' => false])
<div {{ $attributes->class(['space-y-1.5']) }}>
    <label for="{{ $id }}" class="block text-sm font-medium text-ink">
        {{ $label }}@if($required)<span class="ml-0.5 text-accent" aria-hidden="true">*</span>@endif
    </label>
    {{ $slot }}
    @if($hint)
        <p id="{{ $id }}-hint" class="text-xs text-ink-subtle">{{ $hint }}</p>
    @endif
    @if($error)
        <p id="{{ $id }}-error" class="flex items-start gap-1.5 text-xs font-medium text-danger">
            <svg class="mt-px size-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
            <span>{{ $error }}</span>
        </p>
    @endif
</div>
