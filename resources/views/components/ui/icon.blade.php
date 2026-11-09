{{-- Brand icon from public/images/icons that adapts to the dark theme. --}}
@props(['name', 'color' => 'blue', 'size' => 20, 'label' => null])
<img src="{{ asset('images/icons/'.$name.'-'.$color.'.svg') }}" width="{{ $size }}" height="{{ $size }}" alt="{{ $label ?? '' }}"
    {{ $attributes->class(['shrink-0', 'icon-adapt' => $color === 'blue']) }}>
