<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Cambo Hub')</title>
    <meta name="theme-color" content="#0D47A1">
    <link rel="icon" href="{{ asset('images/cambo-hub-mark.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header>
        <a class="brand" href="{{ url('/') }}" aria-label="Cambo Hub home">
            <img src="{{ asset('images/cambo-hub-mark.svg') }}" width="64" height="64" alt="">
            <span class="brand-copy"><span class="brand-name">Cambo<span>Hub</span></span><span class="brand-tagline">Cambodia all-in-one platform</span></span>
        </a>
        <span class="header-caption">Connected to Cambodia.</span>
    </header>
    <main><div class="page-content">
        @if(session('status'))<div class="status" role="status">{{ session('status') }}</div>@endif
        @yield('content')
    </div></main>
    <footer>&copy; {{ date('Y') }} <strong>Cambo<span>Hub</span></strong><span class="footer-divider">&middot;</span>Cambodia all-in-one platform</footer>
</body>
</html>
