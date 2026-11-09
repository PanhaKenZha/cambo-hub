<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · Cambo Hub Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0D47A1">
    <link rel="icon" href="{{ asset('images/cambo-hub-mark.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-svh flex-col overflow-x-hidden bg-[radial-gradient(circle_at_top_left,_rgba(25,118,210,0.08),_transparent_32%),linear-gradient(135deg,_#f8fbff_0%,_#f8fafc_48%,_#f1f5f9_100%)] font-display text-slate-900 antialiased">
    <header class="flex items-center justify-between px-5 py-5 sm:px-8 lg:px-12">
        <a href="{{ route('admin.login') }}" class="group inline-flex items-center gap-2.5" aria-label="Cambo Hub Admin sign in">
            <span class="flex size-9 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-slate-200 transition group-hover:ring-brand-300">
                <img src="{{ asset('images/cambo-hub-mark.svg') }}" class="size-7" alt="">
            </span>
            <span class="text-base font-extrabold tracking-tight text-slate-900">Cambo<span class="text-cambo-red">Hub</span></span>
        </a>
        <span class="hidden items-center gap-2 text-xs font-medium text-slate-400 sm:inline-flex">
            <svg class="size-4 text-emerald-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.86-9.81a.75.75 0 0 0-1.22-.88l-3.24 4.48-1.6-1.6a.75.75 0 1 0-1.06 1.06l2.22 2.22a.75.75 0 0 0 1.14-.09l3.76-5.2Z" clip-rule="evenodd"/></svg>
            Secure admin access
        </span>
    </header>

    <main class="flex flex-1 items-center justify-center px-4 py-8 sm:px-8 sm:py-12">
        <div class="w-full max-w-5xl">
            @if(session('status'))
                <div class="mb-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-600/20" role="status">{{ session('status') }}</div>
            @endif
            @yield('content')
        </div>
    </main>
</body>
</html>
