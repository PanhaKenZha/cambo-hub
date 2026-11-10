{{-- Sub-navigation shared by every Jobs admin page. --}}
@php
    $jobsTabs = [
        ['label' => 'Jobs', 'route' => 'admin.jobs.listings.index', 'active' => 'admin.jobs.listings.*',
            'count' => \App\Models\JobListing::where('status', \App\Enums\JobStatus::InReview)->count()],
        ['label' => 'Companies', 'route' => 'admin.jobs.companies.index', 'active' => 'admin.jobs.companies.*',
            'count' => \App\Models\Company::where('verification_status', \App\Enums\VerificationStatus::Pending)->count()],
        ['label' => 'Categories', 'route' => 'admin.jobs.categories.index', 'active' => 'admin.jobs.categories.*'],
        ['label' => 'Applications', 'route' => 'admin.jobs.applications.index', 'active' => 'admin.jobs.applications.*',
            'count' => \App\Models\JobApplication::where('status', \App\Enums\ApplicationStatus::New)->count()],
    ];
@endphp
<div class="mb-6">
    <div class="flex items-center gap-3">
        <span class="flex size-10 items-center justify-center rounded-xl bg-primary-soft text-primary ring-1 ring-primary/15">
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
        </span>
        <div>
            <p class="text-xs font-semibold tracking-[0.15em] text-ink-subtle uppercase">Module</p>
            <p class="text-lg leading-tight font-bold text-ink">Jobs</p>
        </div>
        @unless(\App\Enums\PlatformModule::Jobs->isEnabled())
            <x-admin.badge color="amber" class="ml-2">Module is turned off — candidates can't see jobs</x-admin.badge>
        @endunless
    </div>
    <nav class="mt-4 flex gap-1 overflow-x-auto border-b border-line" aria-label="Jobs sections">
        @foreach($jobsTabs as $tab)
            @php($isActive = request()->routeIs($tab['active']))
            <a href="{{ route($tab['route']) }}" @if($isActive) aria-current="page" @endif
               @class([
                   '-mb-px flex shrink-0 items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap',
                   'border-primary text-primary' => $isActive,
                   'border-transparent text-ink-muted hover:border-line-strong hover:text-ink' => ! $isActive,
               ])>
                {{ $tab['label'] }}
                @if(($tab['count'] ?? 0) > 0)
                    <span class="rounded-full bg-danger px-2 py-0.5 text-[11px] leading-none font-semibold text-white tabular-nums">{{ $tab['count'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>
</div>
