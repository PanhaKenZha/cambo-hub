@extends('layouts.public')
@php
    use App\Enums\EmploymentType;

    // Builds a job board URL from the current filters with some changed; blank values drop out and paging restarts.
    $filterUrl = fn (array $changes): string => route('jobs.index', array_filter(
        [...request()->only(['q', 'category', 'type', 'remote', 'sort']), ...$changes],
        fn ($value): bool => filled($value),
    ));
    $hasFilters = $term !== '' || $selectedCategory || $selectedType || $remoteOnly;
    $facetClass = 'flex shrink-0 items-center justify-between gap-3 rounded-xl px-3 py-2 text-sm whitespace-nowrap transition lg:whitespace-normal';
@endphp
@section('title', __('Jobs'))
@section('description', __('Browse open jobs from trusted employers across Cambodia and apply online.'))
@section('content')
<section class="jobs-masthead public-page-hero relative overflow-hidden border-b border-line bg-gradient-to-br from-primary-soft via-canvas to-canvas">
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="absolute -top-24 -right-24 size-96 rounded-full bg-brand-400/20 blur-3xl animate-aurora"></div>
        <div class="absolute -bottom-32 left-1/4 size-80 rounded-full bg-cambo-red/10 blur-3xl animate-aurora [animation-delay:-8s]"></div>
        <div class="pattern-khmer absolute inset-0 text-primary/[0.06]"></div>
    </div>
    <img src="{{ asset('frame_00121.webp') }}" width="1920" height="1080" alt="" class="jobs-masthead-image absolute inset-y-0 right-0 hidden h-full w-[42%] object-cover object-[65%_center] lg:block" aria-hidden="true">
    <div class="relative mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:pr-[45%] lg:pl-8">
        <nav aria-label="{{ __('Breadcrumb') }}" class="text-xs text-ink-subtle">
            <a href="{{ route('home') }}" class="hover:text-primary">{{ __('Home') }}</a> <span aria-hidden="true" class="mx-1">/</span> <span aria-current="page" class="text-ink-muted">{{ __('Jobs') }}</span>
        </nav>
        <h1 class="mt-4 max-w-2xl text-3xl leading-tight font-bold tracking-tight sm:text-4xl lg:text-5xl">{{ __('Find your next job in Cambodia') }}</h1>
        <p class="mt-3 max-w-xl text-base text-ink-muted">{{ trans_choice(':count open position from trusted employers.|:count open positions from trusted employers.', $liveJobCount) }}</p>

        <form action="{{ route('jobs.index') }}" method="get" role="search" class="mt-7 flex max-w-2xl items-center gap-2 rounded-2xl border border-line-strong bg-surface p-1.5 pl-4 shadow-card transition focus-within:border-brand-500 focus-within:ring-3 focus-within:ring-brand-500/20">
            <label for="jobs-search" class="sr-only">{{ __('Search jobs') }}</label>
            <x-ui.icon name="search" :size="20" class="size-5 opacity-60" />
            <input id="jobs-search" type="search" name="q" value="{{ $term }}" maxlength="100" placeholder="{{ __('Job title, company or city') }}" autocomplete="off" enterkeyhint="search" class="h-11 min-w-0 flex-1 bg-transparent text-sm text-ink placeholder:text-ink-subtle focus:outline-none">
            @foreach(['category' => $selectedCategory?->slug, 'type' => $selectedType?->value, 'remote' => $remoteOnly ? '1' : null, 'sort' => $sort !== 'newest' ? $sort : null] as $name => $value)
                @if(filled($value))<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endif
            @endforeach
            <x-ui.button class="shrink-0">{{ __('Search') }}</x-ui.button>
        </form>
    </div>
</section>

<div class="mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[16rem_1fr] lg:py-12 lg:px-8">
    <aside class="job-filters min-w-0 space-y-6 rounded-3xl border border-line bg-surface p-5 shadow-card lg:sticky lg:top-24 lg:self-start" aria-label="{{ __('Filter jobs') }}">
        @if($categories->isNotEmpty())
            <div>
                <h2 class="mb-2 text-xs font-semibold tracking-[0.14em] text-ink-subtle uppercase">{{ __('Category') }}</h2>
                <ul class="scrollbar-none -mx-4 flex gap-2 overflow-x-auto px-4 lg:mx-0 lg:flex-col lg:gap-0.5 lg:overflow-visible lg:px-0">
                    <li>
                        <a href="{{ $filterUrl(['category' => null]) }}" @class([$facetClass, 'bg-primary text-on-primary font-semibold' => ! $selectedCategory, 'border border-line bg-surface text-ink-muted hover:text-primary lg:border-0 lg:bg-transparent lg:hover:bg-surface-muted' => $selectedCategory]) @if(! $selectedCategory) aria-current="true" @endif>
                            {{ __('All categories') }}
                        </a>
                    </li>
                    @foreach($categories as $category)
                        @php($isActive = $selectedCategory?->is($category))
                        <li>
                            <a href="{{ $filterUrl(['category' => $category->slug]) }}" @class([$facetClass, 'bg-primary text-on-primary font-semibold' => $isActive, 'border border-line bg-surface text-ink-muted hover:text-primary lg:border-0 lg:bg-transparent lg:hover:bg-surface-muted' => ! $isActive]) @if($isActive) aria-current="true" @endif>
                                <span>{{ $category->translate('name', $locale) }}</span>
                                <span @class(['rounded-full px-2 text-xs tabular-nums', 'bg-white/20' => $isActive, 'bg-surface-muted' => ! $isActive])>{{ $category->job_listings_count }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($typeCounts->isNotEmpty())
            <div>
                <h2 class="mb-2 text-xs font-semibold tracking-[0.14em] text-ink-subtle uppercase">{{ __('Job type') }}</h2>
                <ul class="flex flex-wrap gap-2">
                    @foreach(EmploymentType::cases() as $employmentType)
                        @continue(! $typeCounts->has($employmentType->value))
                        @php($isActive = $selectedType === $employmentType)
                        <li>
                            <a href="{{ $filterUrl(['type' => $isActive ? null : $employmentType->value]) }}" @class(['inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition', 'border-primary bg-primary-soft text-primary' => $isActive, 'border-line bg-surface text-ink-muted hover:border-primary hover:text-primary' => ! $isActive]) @if($isActive) aria-current="true" @endif>
                                {{ __($employmentType->label()) }} <span class="tabular-nums opacity-70">{{ $typeCounts[$employmentType->value] }}</span>
                            </a>
                        </li>
                    @endforeach
                    @if($remoteCount > 0)
                        <li>
                            <a href="{{ $filterUrl(['remote' => $remoteOnly ? null : '1']) }}" @class(['inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition', 'border-success bg-success-soft text-success' => $remoteOnly, 'border-line bg-surface text-ink-muted hover:border-primary hover:text-primary' => ! $remoteOnly]) @if($remoteOnly) aria-current="true" @endif>
                                {{ __('Remote') }} <span class="tabular-nums opacity-70">{{ $remoteCount }}</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        <div class="hidden rounded-2xl border border-line bg-surface p-5 lg:block">
            <p class="text-sm font-semibold text-ink">{{ __('Hiring?') }}</p>
            <p class="mt-1 text-xs leading-relaxed text-ink-muted">{{ __('Reach thousands of job seekers across Cambodia with Cambo Hub Jobs.') }}</p>
            <a href="{{ route('contact') }}" class="mt-3 inline-flex text-xs font-semibold text-primary hover:underline">{{ __('Post a job') }} <span aria-hidden="true" class="ml-1">→</span></a>
        </div>
    </aside>

    <div class="min-w-0">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-sm font-semibold text-ink" aria-live="polite">{{ trans_choice(':count job found|:count jobs found', $jobs->total()) }}</p>
                @if($term !== '')<a href="{{ $filterUrl(['q' => null]) }}" class="inline-flex items-center gap-1 rounded-full bg-surface-muted px-2.5 py-1 text-xs text-ink-muted hover:text-danger">“{{ $term }}” <span aria-hidden="true">×</span><span class="sr-only">{{ __('Remove filter') }}</span></a>@endif
                @if($hasFilters)<a href="{{ route('jobs.index') }}" class="text-xs font-semibold text-primary hover:underline">{{ __('Clear all') }}</a>@endif
            </div>
            <div class="flex items-center gap-1 rounded-xl border border-line bg-surface p-1 text-xs font-semibold" role="group" aria-label="{{ __('Sort jobs') }}">
                @foreach(['newest' => __('Newest'), 'closing' => __('Closing soon')] as $value => $label)
                    <a href="{{ $filterUrl(['sort' => $value === 'newest' ? null : $value]) }}" @class(['rounded-lg px-3 py-1.5 transition', 'bg-primary-soft text-primary' => $sort === $value, 'text-ink-muted hover:text-ink' => $sort !== $value]) @if($sort === $value) aria-current="true" @endif>{{ $label }}</a>
                @endforeach
            </div>
        </div>

        @if($jobs->isEmpty())
            <x-ui.empty-state icon="jobs" :title="__('No jobs match your search')" :description="__('Try a different keyword or remove a filter to see more jobs.')">
                @if($hasFilters)<x-ui.button :href="route('jobs.index')" variant="secondary" size="sm">{{ __('Show all jobs') }}</x-ui.button>@endif
            </x-ui.empty-state>
        @else
            <ul class="grid gap-4 md:grid-cols-2">
                @foreach($jobs as $job)
                    <li class="animate-reveal" style="animation-delay: {{ min($loop->index, 8) * 40 }}ms"><x-job-card :job="$job" :locale="$locale" /></li>
                @endforeach
            </ul>
            <x-ui.pagination :paginator="$jobs" class="mt-10" />
        @endif
    </div>
</div>
@endsection
