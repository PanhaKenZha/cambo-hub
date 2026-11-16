{{-- One live job on the job board or in "Similar jobs". Expects company and category to be eager loaded. --}}
@props(['job', 'locale' => null])
@php
    $company = $job->company;
    $postedAt = $job->published_at ?? $job->created_at;
    $closesSoon = $job->closes_at !== null && $job->closes_at->lessThanOrEqualTo(now()->addDays(7));
@endphp
<x-ui.card as="article" padding="p-5" interactive {{ $attributes->class(['flex h-full flex-col gap-4', 'ring-2 ring-accent/30' => $job->isFeaturedNow()]) }}>
    <div class="flex items-start gap-3.5">
        @if($company?->logoUrl())
            <img src="{{ $company->logoUrl() }}" width="52" height="52" alt="" loading="lazy" class="size-13 shrink-0 rounded-xl border border-line bg-surface object-cover">
        @else
            <span class="grid size-13 shrink-0 place-items-center rounded-xl bg-primary-soft text-lg font-bold text-primary" aria-hidden="true">{{ Str::upper(Str::substr($company?->name ?? '?', 0, 1)) }}</span>
        @endif
        <div class="min-w-0 flex-1">
            <h3 class="line-clamp-2 text-base leading-snug font-semibold text-ink">
                <a href="{{ $job->publicUrl() }}" class="stretched-link hover:text-primary">{{ $job->translate('title', $locale) }}</a>
            </h3>
            <p class="mt-0.5 flex items-center gap-1.5 truncate text-sm text-ink-muted">
                <span class="truncate">{{ $company?->name }}</span>
                @if($company?->isVerified())
                    <svg class="size-4 shrink-0 text-success" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.403 12.652a3 3 0 0 0 0-5.304 3 3 0 0 0-3.75-3.751 3 3 0 0 0-5.305 0 3 3 0 0 0-3.751 3.75 3 3 0 0 0 0 5.305 3 3 0 0 0 3.75 3.751 3 3 0 0 0 5.305 0 3 3 0 0 0 3.751-3.75Zm-2.546-4.46a.75.75 0 0 0-1.214-.883l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                    <span class="sr-only">{{ __('Verified company') }}</span>
                @endif
            </p>
        </div>
        @if($job->isFeaturedNow())<x-ui.badge variant="accent" class="shrink-0">{{ __('Featured') }}</x-ui.badge>@endif
    </div>

    <ul class="flex flex-wrap gap-1.5 text-xs" aria-label="{{ __('Job details') }}">
        <li class="inline-flex items-center gap-1 rounded-lg bg-surface-muted px-2.5 py-1 text-ink-muted"><x-ui.icon name="location" :size="13" class="size-3.5" />{{ __($job->locationLabel()) }}</li>
        <li class="inline-flex items-center gap-1 rounded-lg bg-surface-muted px-2.5 py-1 text-ink-muted"><x-ui.icon name="jobs" :size="13" class="size-3.5" />{{ __($job->employment_type->label()) }}</li>
        @if($job->category)<li class="inline-flex items-center rounded-lg bg-primary-soft px-2.5 py-1 font-medium text-primary">{{ $job->category->translate('name', $locale) }}</li>@endif
    </ul>

    <div class="mt-auto flex flex-wrap items-center justify-between gap-x-3 gap-y-1 border-t border-line pt-4">
        <strong class="text-sm text-primary">{{ __($job->salaryLabel()) }}</strong>
        <span @class(['text-xs', 'font-semibold text-warning' => $closesSoon, 'text-ink-subtle' => ! $closesSoon])>
            @if($closesSoon)
                {{ __('Closes :date', ['date' => $job->closes_at->diffForHumans()]) }}
            @else
                <time datetime="{{ $postedAt->toIso8601String() }}">{{ __('Posted :date', ['date' => $postedAt->diffForHumans()]) }}</time>
            @endif
        </span>
    </div>
</x-ui.card>
