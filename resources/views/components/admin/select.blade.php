@props(['name', 'label'])

<div>
    <label for="{{ $attributes->get('id', $name) }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>
    <select
        id="{{ $attributes->get('id', $name) }}"
        name="{{ $name }}"
        {{ $attributes->except('id')->class('mt-1.5 block w-full rounded-lg border-0 bg-white py-2.5 pr-10 pl-3.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-brand-500 focus:outline-none') }}
    >{{ $slot }}</select>
    @error($name)
        <p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>
    @enderror
</div>
