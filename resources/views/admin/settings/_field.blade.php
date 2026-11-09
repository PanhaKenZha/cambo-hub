{{-- One setting field. Expects: $name, $field (definition), $value, $canEdit --}}
@php($inputValue = old($name, $value))

@switch($field['type'])
    @case('boolean')
        <div class="flex items-start justify-between gap-6 py-1">
            <div>
                <label for="{{ $name }}" class="text-sm font-medium text-slate-800">{{ $field['label'] }}</label>
                @isset($field['help'])<p class="mt-0.5 text-sm text-slate-500">{{ $field['help'] }}</p>@endisset
            </div>
            <label class="relative inline-flex shrink-0 cursor-pointer items-center has-disabled:cursor-not-allowed has-disabled:opacity-60">
                <input type="hidden" name="{{ $name }}" value="0">
                <input type="checkbox" id="{{ $name }}" name="{{ $name }}" value="1" class="peer sr-only" @checked((bool) $inputValue) @disabled(! $canEdit)>
                <span class="h-6 w-11 rounded-full bg-slate-200 transition peer-checked:bg-brand-600 peer-focus-visible:ring-2 peer-focus-visible:ring-brand-500 peer-focus-visible:ring-offset-2 after:absolute after:top-0.5 after:left-0.5 after:size-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-5" aria-hidden="true"></span>
            </label>
        </div>
        @error($name)<p class="text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
        @break

    @case('textarea')
        <div>
            <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">{{ $field['label'] }}</label>
            <textarea id="{{ $name }}" name="{{ $name }}" rows="3" @disabled(! $canEdit) placeholder="{{ $field['placeholder'] ?? '' }}"
                      @class([
                          'mt-1.5 block w-full rounded-lg border-0 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:outline-none focus:ring-inset disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500',
                          'ring-red-400 focus:ring-red-500' => $errors->has($name),
                          'ring-slate-300 focus:ring-brand-500' => ! $errors->has($name),
                      ])>{{ $inputValue }}</textarea>
            @isset($field['help'])<p class="mt-1.5 text-xs text-slate-500">{{ $field['help'] }}</p>@endisset
            @error($name)<p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
        </div>
        @break

    @case('select')
        <div>
            <x-admin.select :name="$name" :label="$field['label']" :disabled="! $canEdit">
                @foreach($field['options'] as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" @selected((string) $inputValue === (string) $optionValue)>{{ $optionLabel }}</option>
                @endforeach
            </x-admin.select>
            @isset($field['help'])<p class="mt-1.5 text-xs text-slate-500">{{ $field['help'] }}</p>@endisset
        </div>
        @break

    @case('multiselect')
        <fieldset>
            <legend class="block text-sm font-medium text-slate-700">{{ $field['label'] }}</legend>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($field['options'] as $optionValue => $optionLabel)
                    <label class="has-disabled:cursor-not-allowed has-disabled:opacity-60">
                        <input type="checkbox" name="{{ $name }}[]" value="{{ $optionValue }}" class="peer sr-only"
                               @checked(in_array($optionValue, (array) $inputValue, true)) @disabled(! $canEdit)>
                        <span class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 text-sm text-slate-600 ring-1 ring-slate-300 transition select-none peer-checked:bg-brand-50 peer-checked:text-brand-700 peer-checked:ring-brand-500 peer-focus-visible:ring-2 hover:bg-slate-50">
                            {{ $optionLabel }}
                        </span>
                    </label>
                @endforeach
            </div>
            @isset($field['help'])<p class="mt-1.5 text-xs text-slate-500">{{ $field['help'] }}</p>@endisset
            @error($name)<p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
            @error($name.'.*')<p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
        </fieldset>
        @break

    @default
        <x-admin.input :name="$name" :label="$field['label']" :type="$field['type']" :value="$value"
                       :hint="$field['help'] ?? null" :placeholder="$field['placeholder'] ?? null" :disabled="! $canEdit" />
@endswitch
