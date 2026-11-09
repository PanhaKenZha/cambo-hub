@props(['name' => null, 'id' => null, 'value' => '1', 'checked' => false])
@php($id ??= $name ?? 'checkbox-'.uniqid())
<label for="{{ $id }}" class="flex cursor-pointer items-start gap-3 text-sm text-ink-muted">
    <input id="{{ $id }}" type="checkbox" @if($name) name="{{ $name }}" @endif value="{{ $value }}" @checked($checked)
        {{ $attributes->class(['mt-0.5 size-4 shrink-0 cursor-pointer rounded border-line-strong accent-brand-700 dark:accent-brand-300']) }}>
    <span>{{ $slot }}</span>
</label>
