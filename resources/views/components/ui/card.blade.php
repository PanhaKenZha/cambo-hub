@props(['as' => 'div', 'padding' => 'p-6 sm:p-8', 'interactive' => false])
<{{ $as }} {{ $attributes->class([
    'relative rounded-2xl border border-line bg-surface shadow-card',
    $padding,
    'transition duration-200 hover:-translate-y-0.5 hover:shadow-card-hover motion-reduce:hover:translate-y-0' => $interactive,
]) }}>
    {{ $slot }}
</{{ $as }}>
