@props(['as' => 'div', 'padding' => 'p-6 sm:p-8', 'interactive' => false])
<{{ $as }} {{ $attributes->class([
    'public-card relative rounded-2xl border border-line bg-surface shadow-card',
    $padding,
    'spotlight transition duration-300 hover:-translate-y-1 hover:border-primary/30 hover:shadow-card-hover motion-reduce:hover:translate-y-0' => $interactive,
]) }}>
    {{ $slot }}
</{{ $as }}>
