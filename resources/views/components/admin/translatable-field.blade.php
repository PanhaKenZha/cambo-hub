{{--
    English + Khmer inputs for one translatable field.
    Usage: <x-admin.translatable-field name="title" label="Title" :value="$page?->title" required />
           <x-admin.translatable-field name="body" label="Content" type="textarea" rows="14" :value="$page?->body" />
--}}
@props(['name', 'label', 'value' => null, 'type' => 'text', 'rows' => 3, 'required' => false, 'maxlength' => null, 'hint' => null, 'placeholder' => null])

@php
    $locales = ['en' => 'English', 'km' => 'ខ្មែរ (Khmer)'];
    $values = (array) ($value ?? []);
    $inputClasses = 'form-control mt-1.5 rounded-lg shadow-sm';
    $hintId = $hint ? "{$name}-hint" : null;
@endphp

<fieldset {{ $attributes->class('grid gap-4 lg:grid-cols-2') }}>
    <legend class="sr-only">{{ $label }}</legend>
    @foreach($locales as $locale => $languageName)
        @php
            $field = "{$name}[{$locale}]";
            $errorKey = "{$name}.{$locale}";
            $inputId = "{$name}_{$locale}";
            $error = $errors->first($errorKey) ?: null;
            $describedBy = trim(($hintId ? $hintId.' ' : '').($error ? $inputId.'-error' : ''));
        @endphp
        <div>
            <label for="{{ $inputId }}" class="flex items-center gap-2 text-sm font-medium text-ink">
                {{ $label }}
                <span class="rounded bg-surface-muted px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-ink-muted uppercase ring-1 ring-line" title="{{ $languageName }}">{{ $locale }}</span>
                @if($required && $locale === 'en')<span class="text-accent" aria-hidden="true">*</span>@endif
            </label>
            @if($type === 'textarea')
                <textarea id="{{ $inputId }}" name="{{ $field }}" rows="{{ $rows }}" @if($maxlength) maxlength="{{ $maxlength }}" @endif
                          placeholder="{{ $placeholder ?? ($locale === 'km' ? 'Optional — English is shown when empty' : '') }}"
                          @if($required && $locale === 'en') required @endif
                          @if($error) aria-invalid="true" @endif
                          @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
                          @class([$inputClasses, 'font-mono leading-relaxed' => $rows > 6])>{{ old("{$name}.{$locale}", $values[$locale] ?? '') }}</textarea>
            @else
                <input id="{{ $inputId }}" name="{{ $field }}" type="text" value="{{ old("{$name}.{$locale}", $values[$locale] ?? '') }}"
                       @if($maxlength) maxlength="{{ $maxlength }}" @endif
                       placeholder="{{ $placeholder ?? ($locale === 'km' ? 'Optional — English is shown when empty' : '') }}"
                       @if($required && $locale === 'en') required @endif
                       @if($error) aria-invalid="true" @endif
                       @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
                       class="{{ $inputClasses }}">
            @endif
            @if($error)
                <p id="{{ $inputId }}-error" class="mt-1.5 flex items-start gap-1.5 text-sm font-medium text-danger" role="alert">
                    <svg class="mt-0.5 size-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                    <span>{{ $error }}</span>
                </p>
            @endif
        </div>
    @endforeach
    @if($hint)
        <p id="{{ $hintId }}" class="-mt-2 text-xs text-ink-subtle lg:col-span-2">{{ $hint }}</p>
    @endif
</fieldset>
