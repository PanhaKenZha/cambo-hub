@php
    use App\Enums\PermissionAction;
    use App\Enums\PermissionArea;

    $currentAdmin = auth()->user();
    $navigation = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'permission' => PermissionArea::Dashboard->permission(PermissionAction::View),
            'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z'],
        ['heading' => 'Security'],
        ['label' => 'Administrators', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'permission' => PermissionArea::Administrators->permission(PermissionAction::View),
            'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
        ['label' => 'Roles & permissions', 'route' => 'admin.roles.index', 'active' => 'admin.roles.*', 'permission' => PermissionArea::Roles->permission(PermissionAction::View),
            'icon' => 'M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.03 5.91c-.56-.1-1.16.02-1.56.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.82c0-.6.24-1.17.66-1.59l6.16-6.16c.41-.4.53-1 .43-1.56A6 6 0 1121.75 8.25z'],
        ['label' => 'Audit logs', 'route' => 'admin.audit-logs.index', 'active' => 'admin.audit-logs.*', 'permission' => PermissionArea::AuditLogs->permission(PermissionAction::View),
            'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'],
    ];
    $visibleNavigation = collect($navigation)
        ->filter(fn (array $item) => isset($item['heading']) || $currentAdmin->can($item['permission']))
        ->filter(fn (array $item, int $index) => ! isset($item['heading']) || collect($navigation)->slice($index + 1)->takeUntil(fn ($next) => isset($next['heading']))->contains(fn ($next) => $currentAdmin->can($next['permission'])));
    $initials = \Illuminate\Support\Str::of($currentAdmin->name)->explode(' ')->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100/70">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · Cambo Hub Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0D47A1">
    <link rel="icon" href="{{ asset('images/branding/cambo-hub-mark.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="h-full font-display text-slate-900 antialiased">
    <input type="checkbox" id="admin-nav-toggle" class="peer sr-only">

    {{-- Mobile backdrop --}}
    <label for="admin-nav-toggle" class="fixed inset-0 z-30 hidden bg-slate-900/50 peer-checked:block lg:hidden!" aria-hidden="true"></label>

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-brand-900 transition-transform peer-checked:translate-x-0 lg:translate-x-0">
        <div class="flex h-16 shrink-0 items-center gap-2.5 border-b border-white/10 px-5">
            <span class="flex size-9 items-center justify-center rounded-xl bg-white">
                <img src="{{ asset('images/branding/cambo-hub-mark.svg') }}" class="size-7" alt="">
            </span>
            <span>
                <span class="block text-lg leading-tight font-extrabold tracking-tight text-white">Cambo<span class="text-red-400">Hub</span></span>
                <span class="block text-[9px] font-medium tracking-[0.2em] text-brand-300 uppercase">Admin console</span>
            </span>
        </div>

        <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-5" aria-label="Admin navigation">
            @foreach($visibleNavigation as $item)
                @if(isset($item['heading']))
                    <p class="px-3 pt-6 pb-2 text-[10px] font-semibold tracking-[0.15em] text-brand-400 uppercase">{{ $item['heading'] }}</p>
                @else
                    @php($isActive = request()->routeIs($item['active']))
                    <a href="{{ route($item['route']) }}" @if($isActive) aria-current="page" @endif
                       @class([
                           'relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                           'bg-white/10 text-white before:absolute before:inset-y-2 before:-left-3 before:w-1 before:rounded-r-full before:bg-red-400' => $isActive,
                           'text-brand-200 hover:bg-white/5 hover:text-white' => ! $isActive,
                       ])>
                        <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-3">
            <div class="flex items-center gap-3 rounded-lg px-2 py-2">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-white/10 text-xs font-semibold text-white ring-1 ring-white/20">{{ $initials }}</span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-white">{{ $currentAdmin->name }}</p>
                    <p class="truncate text-xs text-brand-300">{{ $currentAdmin->email }}</p>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex min-h-full flex-col lg:pl-64">
        {{-- Top bar --}}
        <header class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-4 border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8">
            <label for="admin-nav-toggle" class="-ml-1 cursor-pointer rounded-md p-1.5 text-slate-600 hover:bg-slate-100 lg:hidden">
                <span class="sr-only">Open navigation</span>
                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            </label>

            <nav class="flex min-w-0 flex-1 items-center gap-2 text-sm" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="hidden text-slate-400 hover:text-slate-600 sm:block">Admin</a>
                <svg class="hidden size-4 shrink-0 text-slate-300 sm:block" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                <span class="truncate font-semibold text-slate-800">@yield('title', 'Admin')</span>
            </nav>

            <details class="group relative">
                <summary class="flex cursor-pointer list-none items-center gap-3 rounded-full py-1 pr-2 pl-1 hover:bg-slate-100">
                    <span class="flex size-9 items-center justify-center rounded-full bg-brand-700 text-xs font-semibold text-white">{{ $initials }}</span>
                    <span class="hidden text-left sm:block">
                        <span class="block text-sm leading-tight font-semibold text-slate-900">{{ $currentAdmin->name }}</span>
                        <span class="block text-xs text-slate-500">{{ $currentAdmin->roleLabel() }}</span>
                    </span>
                    <svg class="size-4 text-slate-400 transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                </summary>
                <div class="absolute right-0 mt-2 w-64 overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-slate-200">
                    <div class="border-b border-slate-100 px-4 py-3">
                        <p class="truncate text-sm font-semibold text-slate-900">{{ $currentAdmin->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $currentAdmin->email }}</p>
                    </div>
                    <div class="p-1.5">
                        @unless($currentAdmin->hasTwoFactorEnabled())
                            <a href="{{ route('admin.two-factor.setup') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                <svg class="size-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.04A11.96 11.96 0 013.6 6 12 12 0 003 9.75c0 5.6 3.82 10.3 9 11.62 5.18-1.33 9-6.03 9-11.62 0-1.31-.21-2.57-.6-3.75h-.15c-3.2 0-6.1-1.25-8.25-3.29z"/></svg>
                                Set up two-factor
                            </a>
                        @endunless
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full cursor-pointer items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                <svg class="size-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </details>
        </header>

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            @unless($currentAdmin->hasTwoFactorEnabled())
                <div class="mb-6 flex flex-col gap-3 rounded-xl border-l-4 border-amber-400 bg-amber-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between" role="status">
                    <p class="flex items-center gap-2.5 text-sm text-amber-900">
                        <svg class="size-5 shrink-0 text-amber-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.49 2.87a1.75 1.75 0 0 1 3.02 0l6.28 10.88A1.75 1.75 0 0 1 16.28 16.4H3.72a1.75 1.75 0 0 1-1.51-2.63L8.49 2.87ZM10 7a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 10 7Zm0 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                        Two-factor authentication is not set up for your account.
                    </p>
                    <a href="{{ route('admin.two-factor.setup') }}" class="inline-flex items-center rounded-lg bg-amber-100 px-3 py-1.5 text-sm font-semibold whitespace-nowrap text-amber-900 hover:bg-amber-200">Set it up now &rarr;</a>
                </div>
            @endunless
            @if(session('status'))
                <div class="mb-6 flex items-center gap-2.5 rounded-xl border-l-4 border-emerald-400 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                    <svg class="size-5 shrink-0 text-emerald-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.86-9.81a.75.75 0 0 0-1.22-.88l-3.24 4.48-1.6-1.6a.75.75 0 1 0-1.06 1.06l2.22 2.22a.75.75 0 0 0 1.14-.09l3.76-5.2Z" clip-rule="evenodd"/></svg>
                    {{ session('status') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
