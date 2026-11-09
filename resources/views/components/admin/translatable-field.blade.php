{{--
    English + Khmer inputs for one translatable field.
    Usage: <x-admin.translatable-field name="title" label="Title" :value="$page?->title" required />
           <x-admin.translatable-field name="body" label="Content" type="textarea" rows="14" :value="$page?->body" />
--}}
@props(['name', 'label', 'value' => null, 'type' => 'text', 'rows' => 3, 'required' => false, 'maxlength' => null, 'hint' => null, 'placeholder' => null])

@php
    $locales = ['en' => 'English', 'km' => 'ខ្មែរ (Khmer)'];
    $values = (array) ($value ?? []);
    $inputClasses = 'mt-1.5 block w-full rounded-lg border-0 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:outline-none focus:ring-inset';
@endphp

<fieldset {{ $attributes->class('grid gap-4 lg:grid-cols-2') }}>
    <legend class="sr-only">{{ $label }}</legend>
    @foreach($locales as $locale => $languageName)
        @php($field = "{$name}[{$locale}]")
        @php($errorKey = "{$name}.{$locale}")
        <div>
            <label for="{{ $name }}_{{ $locale }}" class="flex items-center gap-2 text-sm font-medium text-slate-700">
                {{ $label }}
                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-slate-500 uppercase">{{ $locale }}</span>
                @if($required && $locale === 'en')<span class="text-red-500" aria-hidden="true">*</span>@endif
            </label>
            @if($type === 'textarea')
                <textarea id="{{ $name }}_{{ $locale }}" name="{{ $field }}" rows="{{ $rows }}" @if($maxlength) maxlength="{{ $maxlength }}" @endif
                          placeholder="{{ $placeholder ?? ($locale === 'km' ? 'Optional — English is shown when empty' : '') }}"
                          @if($required && $locale === 'en') required @endif
                          @class([$inputClasses, 'font-mono leading-relaxed' => $rows > 6, 'ring-red-400' => $errors->has($errorKey), 'ring-slate-300 focus:ring-brand-500' => ! $errors->has($errorKey)])>{{ old("{$name}.{$locale}", $values[$locale] ?? '') }}</textarea>
            @else
                <input id="{{ $name }}_{{ $locale }}" name="{{ $field }}" type="text" value="{{ old("{$name}.{$locale}", $values[$locale] ?? '') }}"
                       @if($maxlength) maxlength="{{ $maxlength }}" @endif
                       placeholder="{{ $placeholder ?? ($locale === 'km' ? 'Optional — English is shown when empty' : '') }}"
                       @if($required && $locale === 'en') required @endif
                       @class([$inputClasses, 'ring-red-400' => $errors->has($errorKey), 'ring-slate-300 focus:ring-brand-500' => ! $errors->has($errorKey)])>
            @endif
            @error($errorKey)<p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
        </div>
    @endforeach
    @if($hint)
        <p class="-mt-2 text-xs text-slate-500 lg:col-span-2">{{ $hint }}</p>
    @endif
</fieldset>
