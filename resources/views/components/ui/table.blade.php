{{--
    Responsive table: scrolls horizontally when wide, or with "stack" turns each row
    into a labelled card on phones (give every <td> a data-label).
--}}
@props(['label', 'stack' => true])
<div role="region" aria-label="{{ $label }}" tabindex="0" {{ $attributes->class(['overflow-x-auto rounded-2xl border border-line bg-surface shadow-card']) }}>
    <table @class(['w-full text-left text-sm', 'table-stack' => $stack, 'min-w-[36rem]' => ! $stack])>
        <caption class="sr-only">{{ $label }}</caption>
        @isset($head)
            <thead class="border-b border-line bg-surface-muted text-xs font-semibold tracking-wide text-ink-subtle uppercase [&_th]:px-4 [&_th]:py-3 [&_th]:font-semibold">
                {{ $head }}
            </thead>
        @endisset
        <tbody class="divide-y divide-line text-ink-muted sm:[&_td]:px-4 sm:[&_td]:py-3.5">
            {{ $slot }}
        </tbody>
    </table>
</div>
