@props(['name', 'label', 'id' => null, 'value' => null, 'hint' => null, 'bag' => 'default', 'required' => false, 'rows' => 5])
@php
    $id ??= $name;
    $error = $errors->getBag($bag)->first($name) ?: null;
    $describedBy = trim(($hint ? $id.'-hint ' : '').($error ? $id.'-error' : ''));
@endphp
<x-ui.field :id="$id" :label="$label" :hint="$hint" :error="$error" :required="$required" :class="$attributes->get('class')">
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @required($required) @if($error) aria-invalid="true" @endif @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except('class')->class(['form-control min-h-32 resize-y']) }}>{{ old($name, $value) }}</textarea>
</x-ui.field>
