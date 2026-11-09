@props(['title', 'description' => null, 'khmerText' => '', 'robots' => null])
@php
    $locale = app()->getLocale();
    $families = ['Poppins:wght@400;500;600;700;800'];
    $families[] = match ($locale) {
        'km' => 'Kantumruy+Pro:wght@400;500;600;700',
        'zh' => 'Noto+Sans+SC:wght@400;500;700',
        'ja' => 'Noto+Sans+JP:wght@400;500;700',
        default => null,
    };
    $fontUrl = 'https://fonts.googleapis.com/css2?family='.implode('&family=', array_filter($families)).'&display=swap';
    // Outside Khmer pages only a few Khmer words appear (brand tagline, language name), so load just those glyphs.
    $khmerSubsetUrl = $locale !== 'km' && $khmerText !== ''
        ? 'https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@500&display=swap&text='.rawurlencode($khmerText)
        : null;
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title }}</title>
@if($description)<meta name="description" content="{{ $description }}">@endif
@if($robots)<meta name="robots" content="{{ $robots }}">@endif
<meta name="color-scheme" content="light dark">
<meta name="theme-color" content="#0D47A1" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#0A1428" media="(prefers-color-scheme: dark)">
<link rel="icon" href="{{ asset('images/branding/cambo-hub-mark.svg') }}" type="image/svg+xml">
<script>
    (function () {
        try {
            var stored = localStorage.getItem('theme');
            var dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', dark);
        } catch (error) {}
    })();
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="{{ $fontUrl }}">
@if($khmerSubsetUrl)<link rel="stylesheet" href="{{ $khmerSubsetUrl }}">@endif
@vite(['resources/css/app.css', 'resources/js/app.js'])
{{ $slot }}
