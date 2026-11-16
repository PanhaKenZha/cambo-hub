@extends('layouts.public')
@php
    use App\Enums\ApplyMethod;

    $title = $job->translate('title', $locale);
    $company = $job->company;
@endphp
@section('title', $title.' · '.$company->name)
@section('description', Str::limit(strip_tags($job->descriptionHtml($locale)), 160))
@section('content')
<div class="job-detail-masthead public-page-hero border-b border-line bg-gradient-to-br from-primary-soft via-canvas to-canvas">
<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
    <nav aria-label="{{ __('Breadcrumb') }}" class="text-xs text-ink-subtle">
        <a href="{{ route('home') }}" class="hover:text-primary">{{ __('Home') }}</a> <span aria-hidden="true" class="mx-1">/</span>
        <a href="{{ route('jobs.index') }}" class="hover:text-primary">{{ __('Jobs') }}</a>
        @if($job->category)
            <span aria-hidden="true" class="mx-1">/</span> <a href="{{ route('jobs.index', ['category' => $job->category->slug]) }}" class="hover:text-primary">{{ $job->category->translate('name', $locale) }}</a>
        @endif
    </nav>
    <header class="animate-reveal mt-6 flex flex-col gap-5 sm:flex-row sm:items-start">
        @if($company->logoUrl())
            <img src="{{ $company->logoUrl() }}" alt="" class="size-20 shrink-0 rounded-2xl bg-surface object-cover shadow-card ring-1 ring-line">
        @else
            <span class="flex size-20 shrink-0 items-center justify-center rounded-2xl bg-surface text-3xl font-bold text-primary shadow-card ring-1 ring-line">{{ Str::upper(Str::substr($company->name, 0, 1)) }}</span>
        @endif
        <div class="min-w-0">
            <div class="flex flex-wrap gap-2">
                @if($job->category)<x-ui.badge>{{ $job->category->translate('name', $locale) }}</x-ui.badge>@endif
                @if($job->isFeaturedNow())<x-ui.badge variant="accent">{{ __('Featured') }}</x-ui.badge>@endif
                @if($job->is_remote)<x-ui.badge variant="success">{{ __('Remote') }}</x-ui.badge>@endif
            </div>
            <h1 class="mt-2 text-3xl leading-tight font-bold tracking-tight text-ink sm:text-4xl">{{ $title }}</h1>
            <p class="mt-2 flex flex-wrap items-center gap-x-2 text-lg text-ink-muted">
                {{ $company->name }}
                @if($company->isVerified())<span class="inline-flex items-center gap-1 text-sm font-medium text-success"><svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.403 12.652a3 3 0 0 0 0-5.304 3 3 0 0 0-3.75-3.751 3 3 0 0 0-5.305 0 3 3 0 0 0-3.751 3.75 3 3 0 0 0 0 5.305 3 3 0 0 0 3.75 3.751 3 3 0 0 0 5.305 0 3 3 0 0 0 3.751-3.75Zm-2.546-4.46a.75.75 0 0 0-1.214-.883l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>{{ __('Verified company') }}</span>@endif
            </p>
            <p class="mt-2 text-xs text-ink-subtle">
                @php($postedAt = $job->published_at ?? $job->created_at)
                <time datetime="{{ $postedAt->toIso8601String() }}">{{ __('Posted :date', ['date' => $postedAt->diffForHumans()]) }}</time>
                <span aria-hidden="true" class="mx-1">·</span>{{ trans_choice(':count view|:count views', $job->views_count, ['count' => number_format($job->views_count)]) }}
            </p>
        </div>
    </header>
</div>
</div>
<div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 sm:py-12 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[1fr_22rem]">
        <div class="min-w-0">
            <dl class="grid grid-cols-2 gap-4 rounded-2xl bg-surface p-5 shadow-card ring-1 ring-line sm:grid-cols-4">
                <div><dt class="text-xs text-ink-subtle">{{ __('Salary') }}</dt><dd class="mt-1 text-sm font-semibold text-ink">{{ $job->salaryLabel() }}</dd></div>
                <div><dt class="text-xs text-ink-subtle">{{ __('Job type') }}</dt><dd class="mt-1 text-sm font-semibold text-ink">{{ __($job->employment_type->label()) }}</dd></div>
                <div><dt class="text-xs text-ink-subtle">{{ __('Location') }}</dt><dd class="mt-1 text-sm font-semibold text-ink">{{ $job->locationLabel() }}</dd></div>
                <div><dt class="text-xs text-ink-subtle">{{ __('Apply before') }}</dt><dd class="mt-1 text-sm font-semibold text-ink">{{ $job->closes_at?->translatedFormat('j M Y') ?? __('Open') }}</dd></div>
            </dl>

            <div class="reading-surface cms-prose mt-8">{!! $job->descriptionHtml($locale) !!}</div>

            @if(filled($company->translate('description', $locale)))
                <section class="mt-10 border-t border-line pt-8">
                    <h2 class="text-xl font-bold text-ink">{{ __('About :company', ['company' => $company->name]) }}</h2>
                    <p class="mt-3 text-sm leading-relaxed whitespace-pre-line text-ink-muted">{{ $company->translate('description', $locale) }}</p>
                    @if($company->website)
                        <a href="{{ $company->website }}" target="_blank" rel="noopener nofollow" class="mt-3 inline-block text-sm font-semibold text-primary hover:underline">{{ __('Company website') }} &nearr;</a>
                    @endif
                </section>
            @endif
        </div>

        <aside id="apply" class="lg:sticky lg:top-24 lg:self-start">
            <x-ui.card>
                <h2 class="text-lg font-bold text-ink">{{ __('Apply for this job') }}</h2>

                @if(session('status'))
                    <x-ui.alert variant="success" class="mt-4">{{ session('status') }}</x-ui.alert>
                @endif

                @switch($job->apply_method)
                    @case(ApplyMethod::ExternalUrl)
                        <p class="mt-2 text-sm text-ink-muted">{{ __('This company takes applications on its own website.') }}</p>
                        <x-ui.button class="mt-4 w-full" :href="$job->apply_url" target="_blank" rel="noopener nofollow">{{ __('Apply on company website') }} &nearr;</x-ui.button>
                        @break
                    @case(ApplyMethod::Email)
                        <p class="mt-2 text-sm text-ink-muted">{{ __('Send your CV by email to') }} <span class="font-medium break-all text-ink">{{ $job->apply_email }}</span></p>
                        <x-ui.button class="mt-4 w-full" :href="'mailto:'.$job->apply_email.'?subject='.rawurlencode($title)">{{ __('Apply by email') }}</x-ui.button>
                        @break
                    @default
                        @if($hasApplied)
                            <p class="mt-3 text-sm text-ink-muted">{{ __('You have applied for this job. The company will contact you if your profile fits.') }}</p>
                        @else
                            @auth
                                <form method="POST" action="{{ route('jobs.apply', $job->slug) }}" enctype="multipart/form-data" class="mt-4 space-y-4" data-loading>
                                    @csrf
                                    <x-ui.error-summary />
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-ink">{{ __('Full name') }}</label>
                                        <input id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required maxlength="150" class="form-control mt-1.5 rounded-lg">
                                    </div>
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-ink">{{ __('Email') }}</label>
                                        <input id="email" name="email" type="email" value="{{ old('email', auth()->user()->email) }}" required class="form-control mt-1.5 rounded-lg">
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-ink">{{ __('Phone') }}</label>
                                        <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" maxlength="50" class="form-control mt-1.5 rounded-lg">
                                    </div>
                                    <x-ui.textarea name="cover_letter" :label="__('Cover letter (optional)')" rows="4" />
                                    <div>
                                        <label for="cv" class="block text-sm font-medium text-ink">{{ __('CV') }}</label>
                                        <input id="cv" name="cv" type="file" accept=".pdf,.doc,.docx" required
                                               class="mt-1.5 block w-full text-sm text-ink-muted file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-primary-soft file:px-3 file:py-2 file:text-sm file:font-semibold file:text-primary">
                                        <p class="mt-1.5 text-xs text-ink-subtle">{{ __('PDF or Word. Only the employer\'s reviewers can see it.') }}</p>
                                    </div>
                                    <x-ui.button class="w-full" :loading-text="__('Sending…')">{{ __('Send application') }}</x-ui.button>
                                </form>
                            @else
                                <p class="mt-2 text-sm text-ink-muted">{{ __('Sign in to apply with your CV.') }}</p>
                                <x-ui.button class="mt-4 w-full" :href="route('login')">{{ __('Sign in to apply') }}</x-ui.button>
                            @endauth
                        @endif
                @endswitch
            </x-ui.card>
            <x-report-content type="job" :id="$job->id" class="mt-4 px-1" />
        </aside>
    </div>

    @if($similarJobs->isNotEmpty())
        <section class="mt-14 border-t border-line pt-10" aria-labelledby="similar-jobs-heading">
            <div class="mb-6 flex items-end justify-between gap-4">
                <h2 id="similar-jobs-heading" class="text-2xl font-bold tracking-tight">{{ __('Similar jobs') }}</h2>
                <a href="{{ route('jobs.index', array_filter(['category' => $job->category?->slug])) }}" class="group inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold text-primary">{{ __('View all') }} <span aria-hidden="true" class="transition group-hover:translate-x-0.5">→</span></a>
            </div>
            <ul class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach($similarJobs as $similarJob)
                    <li><x-job-card :job="$similarJob" :locale="$locale" /></li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
@endsection
