<!DOCTYPE html>
<html lang="en" class="h-full bg-canvas">
<head>
    <x-ui.document-head :title="new \Illuminate\Support\HtmlString($__env->yieldContent('title', 'Admin').' · Cambo Hub Admin')" robots="noindex, nofollow" />
</head>
<body class="flex min-h-svh flex-col overflow-x-hidden bg-canvas bg-[radial-gradient(circle_at_top_left,_rgba(25,118,210,0.08),_transparent_32%),linear-gradient(135deg,_#f8fbff_0%,_#f8fafc_48%,_#f1f5f9_100%)] font-display text-ink antialiased dark:bg-[radial-gradient(circle_at_top_left,_rgba(79,143,224,0.14),_transparent_36%)]">
    <header class="flex items-center justify-between gap-4 px-5 py-5 sm:px-8 lg:px-12">
        <a href="{{ route('admin.login') }}" class="group inline-flex items-center gap-2.5 rounded-xl" aria-label="Cambo Hub Admin sign in">
            <span class="flex size-9 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-line transition group-hover:ring-brand-300">
                <img src="{{ asset('images/branding/cambo-hub-mark.svg') }}" class="size-7" alt="">
            </span>
            <span class="text-base font-extrabold tracking-tight text-ink">Cambo<span class="text-cambo-red dark:text-red-400">Hub</span></span>
        </a>
        <div class="flex items-center gap-4">
            <span class="hidden items-center gap-2 text-xs font-medium text-ink-subtle sm:inline-flex">
                <svg class="size-4 text-success" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.86-9.81a.75.75 0 0 0-1.22-.88l-3.24 4.48-1.6-1.6a.75.75 0 1 0-1.06 1.06l2.22 2.22a.75.75 0 0 0 1.14-.09l3.76-5.2Z" clip-rule="evenodd"/></svg>
                Secure admin access
            </span>
            <x-ui.theme-toggle />
        </div>
    </header>

    <main class="flex flex-1 items-center justify-center px-4 py-8 sm:px-8 sm:py-12">
        <div class="w-full max-w-5xl">
            @if(session('status'))
                <div class="mb-6 rounded-lg bg-success-soft px-4 py-3 text-sm text-success ring-1 ring-success/25" role="status">{{ session('status') }}</div>
            @endif
            @yield('content')
        </div>
    </main>
</body>
</html>
