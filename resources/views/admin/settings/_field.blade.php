{{-- One setting field. Expects: $name, $field (definition), $value, $canEdit --}}
@php
    $inputValue = old($name, $value);
    $fieldError = $errors->first($name) ?: ($field['type'] === 'multiselect' ? ($errors->first($name.'.*') ?: null) : null);
    $hintId = isset($field['help']) ? $name.'-hint' : null;
    $describedBy = trim(($hintId ? $hintId.' ' : '').($fieldError ? $name.'-error' : ''));
@endphp

@switch($field['type'])
    @case('boolean')
        <div class="flex items-start justify-between gap-6 py-1">
            <div>
                <label for="{{ $name }}" class="text-sm font-medium text-ink">{{ $field['label'] }}</label>
                @isset($field['help'])<p id="{{ $hintId }}" class="mt-0.5 text-sm text-ink-muted">{{ $field['help'] }}</p>@endisset
            </div>
            <label class="relative inline-flex shrink-0 cursor-pointer items-center has-disabled:cursor-not-allowed has-disabled:opacity-60">
                <input type="hidden" name="{{ $name }}" value="0">
                <input type="checkbox" id="{{ $name }}" name="{{ $name }}" value="1" class="peer sr-only" @checked((bool) $inputValue) @disabled(! $canEdit)
                       @if($fieldError) aria-invalid="true" @endif @if($describedBy) aria-describedby="{{ $describedBy }}" @endif>
                <span class="h-6 w-11 rounded-full bg-line-strong transition peer-checked:bg-primary peer-focus-visible:outline-3 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-500 after:absolute after:top-0.5 after:left-0.5 after:size-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5" aria-hidden="true"></span>
            </label>
        </div>
        @if($fieldError)<p id="{{ $name }}-error" class="text-sm font-medium text-danger" role="alert">{{ $fieldError }}</p>@endif
        @break

    @case('textarea')
        <div>
            <label for="{{ $name }}" class="block text-sm font-medium text-ink">{{ $field['label'] }}</label>
            <textarea id="{{ $name }}" name="{{ $name }}" rows="3" @disabled(! $canEdit) placeholder="{{ $field['placeholder'] ?? '' }}"
                      @if($fieldError) aria-invalid="true" @endif @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
                      class="form-control mt-1.5 rounded-lg shadow-sm disabled:bg-surface-muted disabled:text-ink-muted">{{ $inputValue }}</textarea>
            @isset($field['help'])<p id="{{ $hintId }}" class="mt-1.5 text-xs text-ink-subtle">{{ $field['help'] }}</p>@endisset
            @if($fieldError)<p id="{{ $name }}-error" class="mt-1.5 text-sm font-medium text-danger" role="alert">{{ $fieldError }}</p>@endif
        </div>
        @break

    @case('select')
        <div>
            <x-admin.select :name="$name" :label="$field['label']" :hint="$field['help'] ?? null" :disabled="! $canEdit">
                @foreach($field['options'] as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" @selected((string) $inputValue === (string) $optionValue)>{{ $optionLabel }}</option>
                @endforeach
            </x-admin.select>
        </div>
        @break

    @case('multiselect')
        <fieldset id="{{ $name }}" tabindex="-1" class="focus:outline-none" @if($describedBy) aria-describedby="{{ $describedBy }}" @endif>
            <legend class="block text-sm font-medium text-ink">{{ $field['label'] }}</legend>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($field['options'] as $optionValue => $optionLabel)
                    <label class="has-disabled:cursor-not-allowed has-disabled:opacity-60">
                        <input type="checkbox" name="{{ $name }}[]" value="{{ $optionValue }}" class="peer sr-only"
                               @checked(in_array($optionValue, (array) $inputValue, true)) @disabled(! $canEdit)>
                        <span class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg bg-surface px-3 py-1.5 text-sm text-ink-muted ring-1 ring-line-strong transition select-none hover:bg-surface-muted peer-checked:bg-primary-soft peer-checked:text-primary peer-checked:ring-primary peer-focus-visible:outline-3 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-500">
                            {{ $optionLabel }}
                        </span>
                    </label>
                @endforeach
            </div>
            @isset($field['help'])<p id="{{ $hintId }}" class="mt-1.5 text-xs text-ink-subtle">{{ $field['help'] }}</p>@endisset
            @error($name)<p id="{{ $name }}-error" class="mt-1.5 text-sm font-medium text-danger" role="alert">{{ $message }}</p>@enderror
            @error($name.'.*')<p @unless($errors->has($name)) id="{{ $name }}-error" @endunless class="mt-1.5 text-sm font-medium text-danger" role="alert">{{ $message }}</p>@enderror
        </fieldset>
        @break

    @default
        <x-admin.input :name="$name" :label="$field['label']" :type="$field['type']" :value="$value"
                       :hint="$field['help'] ?? null" :placeholder="$field['placeholder'] ?? null" :disabled="! $canEdit" />
@endswitch
