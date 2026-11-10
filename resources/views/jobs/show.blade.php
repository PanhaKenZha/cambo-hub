@extends('layouts.public')
@php
    use App\Enums\ApplyMethod;

    $title = $job->translate('title', $locale);
    $company = $job->company;
@endphp
@section('title', $title.' · '.$company->name)
@section('description', Str::limit(strip_tags($job->descriptionHtml($locale)), 160))
@section('content')
<div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
    <header class="animate-reveal flex flex-col gap-5 sm:flex-row sm:items-start">
        @if($company->logoUrl())
            <img src="{{ $company->logoUrl() }}" alt="" class="size-16 shrink-0 rounded-2xl object-cover ring-1 ring-line">
        @else
            <span class="flex size-16 shrink-0 items-center justify-center rounded-2xl bg-primary-soft text-2xl font-bold text-primary">{{ Str::upper(Str::substr($company->name, 0, 1)) }}</span>
        @endif
        <div class="min-w-0">
            <div class="flex flex-wrap gap-2">
                @if($job->category)<x-ui.badge>{{ $job->category->translate('name', $locale) }}</x-ui.badge>@endif
                @if($job->isFeaturedNow())<x-ui.badge variant="warning">{{ __('Featured') }}</x-ui.badge>@endif
            </div>
            <h1 class="mt-2 text-3xl leading-tight font-bold tracking-tight text-ink sm:text-4xl">{{ $title }}</h1>
            <p class="mt-2 text-lg text-ink-muted">
                {{ $company->name }}
                @if($company->isVerified())<span class="text-success" title="{{ __('Verified company') }}">✓ <span class="text-sm">{{ __('Verified') }}</span></span>@endif
            </p>
        </div>
    </header>

    <div class="mt-10 grid gap-8 lg:grid-cols-[1fr_20rem]">
        <div class="min-w-0">
            <dl class="grid grid-cols-2 gap-4 rounded-2xl bg-surface p-5 shadow-card ring-1 ring-line sm:grid-cols-4">
                <div><dt class="text-xs text-ink-subtle">{{ __('Salary') }}</dt><dd class="mt-1 text-sm font-semibold text-ink">{{ $job->salaryLabel() }}</dd></div>
                <div><dt class="text-xs text-ink-subtle">{{ __('Job type') }}</dt><dd class="mt-1 text-sm font-semibold text-ink">{{ __($job->employment_type->label()) }}</dd></div>
                <div><dt class="text-xs text-ink-subtle">{{ __('Location') }}</dt><dd class="mt-1 text-sm font-semibold text-ink">{{ $job->locationLabel() }}</dd></div>
                <div><dt class="text-xs text-ink-subtle">{{ __('Apply before') }}</dt><dd class="mt-1 text-sm font-semibold text-ink">{{ $job->closes_at?->translatedFormat('j M Y') ?? __('Open') }}</dd></div>
            </dl>

            <div class="cms-prose mt-8">{!! $job->descriptionHtml($locale) !!}</div>

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
        </aside>
    </div>
</div>
@endsection
