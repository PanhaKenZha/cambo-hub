@php
    use App\Enums\PermissionAction;
    use App\Enums\PermissionArea;

    $currentAdmin = auth()->user();
    $navigation = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'permission' => PermissionArea::Dashboard->permission(PermissionAction::View),
            'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z'],
        ['heading' => 'Website'],
        ['label' => 'Pages', 'route' => 'admin.cms.pages.index', 'active' => 'admin.cms.pages.*', 'permission' => PermissionArea::WebsiteContent->permission(PermissionAction::View),
            'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'],
        ['label' => 'Homepage', 'route' => 'admin.cms.homepage.index', 'active' => 'admin.cms.homepage.*', 'permission' => PermissionArea::WebsiteContent->permission(PermissionAction::View),
            'icon' => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
        ['label' => 'Menus', 'route' => 'admin.cms.menus.index', 'active' => 'admin.cms.menus.*', 'permission' => PermissionArea::WebsiteContent->permission(PermissionAction::View),
            'icon' => 'M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12'],
        ['label' => 'Banners', 'route' => 'admin.cms.banners.index', 'active' => 'admin.cms.banners.*', 'permission' => PermissionArea::WebsiteContent->permission(PermissionAction::View),
            'icon' => 'M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z'],
        ['label' => 'Announcements', 'route' => 'admin.cms.announcements.index', 'active' => 'admin.cms.announcements.*', 'permission' => PermissionArea::WebsiteContent->permission(PermissionAction::View),
            'icon' => 'M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46'],
        ['label' => 'Footer', 'route' => 'admin.settings.edit', 'params' => ['group' => 'footer'], 'permission' => PermissionArea::Settings->permission(PermissionAction::View),
            'isActive' => request()->route('group') === 'footer',
            'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h12A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6zM3.75 15.75h16.5'],
        ['heading' => 'Services'],
        ['label' => 'News', 'route' => 'admin.news.articles.index', 'active' => 'admin.news.*', 'permission' => PermissionArea::News->permission(PermissionAction::View),
            'badge' => fn () => \App\Models\NewsComment::where('status', \App\Enums\CommentStatus::Pending)->count()
                + \App\Models\NewsArticle::where('status', \App\Enums\ArticleStatus::InReview)->count(),
            'icon' => 'M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z'],
        ['heading' => 'Moderation'],
        ['label' => 'Approvals', 'route' => 'admin.approvals.index', 'active' => 'admin.approvals.*', 'permission' => PermissionArea::Approvals->permission(PermissionAction::View),
            'badge' => fn () => \App\Models\ApprovalRequest::pending()->whereIn('module', \App\Enums\PlatformModule::visibleTo($currentAdmin))->count(),
            'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Reports', 'route' => 'admin.reports.index', 'active' => 'admin.reports.*', 'permission' => PermissionArea::Approvals->permission(PermissionAction::View),
            'badge' => fn () => \App\Models\ContentReport::where('status', \App\Enums\ReportStatus::Open)->count(),
            'icon' => 'M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a48.524 48.524 0 01-.005-10.499l-3.11.732a9 9 0 01-6.085-.711l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5'],
        ['label' => 'Violations', 'route' => 'admin.violations.index', 'active' => 'admin.violations.*', 'permission' => PermissionArea::Approvals->permission(PermissionAction::View),
            'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
        ['label' => 'Appeals', 'route' => 'admin.appeals.index', 'active' => 'admin.appeals.*', 'permission' => PermissionArea::Approvals->permission(PermissionAction::View),
            'badge' => fn () => \App\Models\ModerationAppeal::where('status', \App\Enums\AppealStatus::Pending)->count(),
            'icon' => 'M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z'],
        ['heading' => 'Configuration'],
        ['label' => 'Platform settings', 'route' => 'admin.settings.index', 'permission' => PermissionArea::Settings->permission(PermissionAction::View),
            'isActive' => request()->routeIs('admin.settings.*') && ! in_array(request()->route('group'), ['modules', 'footer'], true),
            'icon' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
        ['label' => 'Modules', 'route' => 'admin.settings.edit', 'params' => ['group' => 'modules'], 'permission' => PermissionArea::Settings->permission(PermissionAction::View),
            'isActive' => request()->route('group') === 'modules',
            'icon' => config('settings.modules.icon')],
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
<html lang="en" class="h-full bg-canvas">
<head>
    <x-ui.document-head :title="new \Illuminate\Support\HtmlString($__env->yieldContent('title', 'Admin').' · Cambo Hub Admin')" robots="noindex, nofollow" />
</head>
<body class="h-full bg-canvas font-display text-ink antialiased">
    <a href="#admin-main" class="sr-only z-50 rounded-lg bg-surface px-4 py-2 text-sm font-semibold text-primary shadow-card focus:not-sr-only focus:fixed focus:top-3 focus:left-3">Skip to content</a>

    {{-- Mobile menu state. Its accessible name comes from the "Open navigation" label in the top bar. --}}
    <input type="checkbox" id="admin-nav-toggle" class="peer sr-only lg:hidden" aria-controls="admin-sidebar">

    {{-- Mobile backdrop --}}
    <label for="admin-nav-toggle" class="fixed inset-0 z-30 hidden bg-slate-950/60 peer-checked:block lg:hidden!" aria-hidden="true"></label>

    {{-- Sidebar: hidden from keyboard and screen readers while collapsed off-screen on small viewports --}}
    <aside id="admin-sidebar" class="invisible fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-brand-900 transition-[translate,visibility] peer-checked:visible peer-checked:translate-x-0 lg:visible lg:translate-x-0 dark:border-r dark:border-white/10 dark:bg-brand-950">
        <div class="flex h-16 shrink-0 items-center gap-2.5 border-b border-white/10 px-5">
            <span class="flex size-9 items-center justify-center rounded-xl bg-white">
                <img src="{{ asset('images/branding/cambo-hub-mark.svg') }}" class="size-7" alt="">
            </span>
            <span>
                <span class="block text-lg leading-tight font-extrabold tracking-tight text-white">Cambo<span class="text-red-400">Hub</span></span>
                <span class="block text-[9px] font-medium tracking-[0.2em] text-brand-300 uppercase">Admin console</span>
            </span>
        </div>

        <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-5 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" aria-label="Admin navigation">
            @foreach($visibleNavigation as $item)
                @if(isset($item['heading']))
                    <p class="px-3 pt-6 pb-2 text-[10px] font-semibold tracking-[0.15em] text-brand-400 uppercase">{{ $item['heading'] }}</p>
                @else
                    @php($isActive = $item['isActive'] ?? request()->routeIs($item['active']))
                    <a href="{{ route($item['route'], $item['params'] ?? []) }}" @if($isActive) aria-current="page" @endif
                       @class([
                           'relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-brand-200',
                           'bg-white/10 text-white before:absolute before:inset-y-2 before:-left-3 before:w-1 before:rounded-r-full before:bg-red-400' => $isActive,
                           'text-brand-200 hover:bg-white/5 hover:text-white' => ! $isActive,
                       ])>
                        <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                        <span class="flex-1">{{ $item['label'] }}</span>
                        @isset($item['badge'])
                            @php($badgeCount = $item['badge']())
                            @if($badgeCount > 0)
                                <span class="rounded-full bg-red-500 px-2 py-0.5 text-[11px] leading-none font-semibold text-white tabular-nums">{{ $badgeCount > 99 ? '99+' : $badgeCount }}</span>
                            @endif
                        @endisset
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
        <header class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-3 border-b border-line bg-surface/95 px-4 backdrop-blur sm:gap-4 sm:px-6 lg:px-8">
            <label for="admin-nav-toggle" class="-ml-1 cursor-pointer rounded-md p-1.5 text-ink-muted hover:bg-surface-muted hover:text-ink lg:hidden [body:has(#admin-nav-toggle:focus-visible)_&]:outline-3 [body:has(#admin-nav-toggle:focus-visible)_&]:outline-offset-2 [body:has(#admin-nav-toggle:focus-visible)_&]:outline-brand-500 dark:[body:has(#admin-nav-toggle:focus-visible)_&]:outline-brand-300">
                <span class="sr-only">Open navigation</span>
                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            </label>

            <nav class="flex min-w-0 flex-1 items-center gap-2 text-sm" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="hidden rounded text-ink-subtle hover:text-ink sm:block">Admin</a>
                <svg class="hidden size-4 shrink-0 text-line-strong sm:block" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                <span class="truncate font-semibold text-ink" aria-current="page">@yield('title', 'Admin')</span>
            </nav>

            <x-ui.theme-toggle />

            <details class="group relative">
                <summary class="flex cursor-pointer list-none items-center gap-3 rounded-full py-1 pr-2 pl-1 hover:bg-surface-muted [&::-webkit-details-marker]:hidden">
                    <span class="flex size-9 items-center justify-center rounded-full bg-brand-700 text-xs font-semibold text-white dark:bg-brand-500">{{ $initials }}</span>
                    <span class="sr-only sm:hidden">{{ $currentAdmin->name }}</span>
                    <span class="hidden text-left sm:block">
                        <span class="block text-sm leading-tight font-semibold text-ink">{{ $currentAdmin->name }}</span>
                        <span class="block text-xs text-ink-muted">{{ $currentAdmin->roleLabel() }}</span>
                    </span>
                    <svg class="size-4 text-ink-subtle transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                </summary>
                <div class="absolute right-0 z-30 mt-2 w-64 max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl bg-surface-raised shadow-lg ring-1 ring-line">
                    <div class="border-b border-line px-4 py-3">
                        <p class="truncate text-sm font-semibold text-ink">{{ $currentAdmin->name }}</p>
                        <p class="truncate text-xs text-ink-muted">{{ $currentAdmin->email }}</p>
                    </div>
                    <div class="p-1.5">
                        @unless($currentAdmin->hasTwoFactorEnabled())
                            <a href="{{ route('admin.two-factor.setup') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-ink-muted hover:bg-surface-muted hover:text-ink">
                                <svg class="size-5 text-ink-subtle" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.04A11.96 11.96 0 013.6 6 12 12 0 003 9.75c0 5.6 3.82 10.3 9 11.62 5.18-1.33 9-6.03 9-11.62 0-1.31-.21-2.57-.6-3.75h-.15c-3.2 0-6.1-1.25-8.25-3.29z"/></svg>
                                Set up two-factor
                            </a>
                        @endunless
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full cursor-pointer items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-ink-muted hover:bg-surface-muted hover:text-ink">
                                <svg class="size-5 text-ink-subtle" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </details>
        </header>

        <main id="admin-main" tabindex="-1" class="flex-1 px-4 py-6 focus:outline-none sm:px-6 lg:px-8">
            @if(app(\App\Services\PlatformSettings::class)->get('maintenance.enabled'))
                <div class="mb-6 flex flex-col gap-3 rounded-xl border-l-4 border-danger bg-danger-soft px-4 py-3 sm:flex-row sm:items-center sm:justify-between" role="status">
                    <p class="flex items-center gap-2.5 text-sm text-ink">
                        <svg class="size-5 shrink-0 text-danger" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                        Maintenance mode is on. The public website is closed to visitors.
                    </p>
                    @can(\App\Enums\PermissionArea::Settings->permission(\App\Enums\PermissionAction::View))
                        <a href="{{ route('admin.settings.edit', 'maintenance') }}" class="inline-flex items-center self-start rounded-lg bg-danger/15 px-3 py-1.5 text-sm font-semibold whitespace-nowrap text-danger hover:bg-danger/25 sm:self-auto">Maintenance settings &rarr;</a>
                    @endcan
                </div>
            @endif
            @unless($currentAdmin->hasTwoFactorEnabled())
                <div class="mb-6 flex flex-col gap-3 rounded-xl border-l-4 border-warning bg-warning-soft px-4 py-3 sm:flex-row sm:items-center sm:justify-between" role="status">
                    <p class="flex items-center gap-2.5 text-sm text-ink">
                        <svg class="size-5 shrink-0 text-warning" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.49 2.87a1.75 1.75 0 0 1 3.02 0l6.28 10.88A1.75 1.75 0 0 1 16.28 16.4H3.72a1.75 1.75 0 0 1-1.51-2.63L8.49 2.87ZM10 7a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 10 7Zm0 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                        Two-factor authentication is not set up for your account.
                    </p>
                    <a href="{{ route('admin.two-factor.setup') }}" class="inline-flex items-center self-start rounded-lg bg-warning/15 px-3 py-1.5 text-sm font-semibold whitespace-nowrap text-warning hover:bg-warning/25 sm:self-auto">Set it up now &rarr;</a>
                </div>
            @endunless
            @if(session('status'))
                <div class="mb-6 flex items-center gap-2.5 rounded-xl border-l-4 border-success bg-success-soft px-4 py-3 text-sm text-success" role="status">
                    <svg class="size-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.86-9.81a.75.75 0 0 0-1.22-.88l-3.24 4.48-1.6-1.6a.75.75 0 1 0-1.06 1.06l2.22 2.22a.75.75 0 0 0 1.14-.09l3.76-5.2Z" clip-rule="evenodd"/></svg>
                    {{ session('status') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
