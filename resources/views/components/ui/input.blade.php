@props(['name', 'label', 'type' => 'text', 'id' => null, 'value' => null, 'hint' => null, 'bag' => 'default', 'required' => false])
@php
    $id ??= $name;
    $error = $errors->getBag($bag)->first($name) ?: null;
    $describedBy = trim(($hint ? $id.'-hint ' : '').($error ? $id.'-error' : ''));
    $value = $type === 'password' ? null : old($name, $value);
@endphp
<x-ui.field :id="$id" :label="$label" :hint="$hint" :error="$error" :required="$required" :class="$attributes->get('class')">
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" @if($value !== null) value="{{ $value }}" @endif
        @required($required) @if($error) aria-invalid="true" @endif @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except('class')->class(['form-control']) }}>
</x-ui.field>
