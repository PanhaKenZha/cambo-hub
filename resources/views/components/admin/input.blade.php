@props(['name', 'label', 'type' => 'text', 'value' => null, 'errorBag' => 'default', 'hint' => null])

<div>
    <label for="{{ $attributes->get('id', $name) }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>
    <input
        id="{{ $attributes->get('id', $name) }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if($type !== 'password') value="{{ old($name, $value) }}" @endif
        {{ $attributes->except('id')->class([
            'mt-1.5 block w-full rounded-lg border-0 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:outline-none',
            'ring-red-400 focus:ring-red-500' => $errors->getBag($errorBag)->has($name),
            'ring-slate-300 focus:ring-brand-500' => ! $errors->getBag($errorBag)->has($name),
        ]) }}
    >
    @if($hint)
        <p class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif
    @error($name, $errorBag)
        <p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>
    @enderror
</div>
