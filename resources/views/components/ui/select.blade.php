@props(['name', 'label', 'options' => [], 'placeholder' => null, 'id' => null, 'value' => null, 'hint' => null, 'bag' => 'default', 'required' => false])
@php
    $id ??= $name;
    $error = $errors->getBag($bag)->first($name) ?: null;
    $describedBy = trim(($hint ? $id.'-hint ' : '').($error ? $id.'-error' : ''));
    $selected = (string) old($name, $value);
@endphp
<x-ui.field :id="$id" :label="$label" :hint="$hint" :error="$error" :required="$required" :class="$attributes->get('class')">
    <div class="relative">
        <select id="{{ $id }}" name="{{ $name }}" @required($required) @if($error) aria-invalid="true" @endif @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->except('class')->class(['form-control appearance-none pr-10']) }}>
            @if($placeholder)<option value="">{{ $placeholder }}</option>@endif
            @foreach($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $optionLabel }}</option>
            @endforeach
        </select>
        <svg class="pointer-events-none absolute top-1/2 right-3.5 size-4 -translate-y-1/2 text-ink-subtle" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
    </div>
</x-ui.field>
