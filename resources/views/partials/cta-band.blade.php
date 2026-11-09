{{-- Dark call-to-action band. Expects: eyebrow, heading, body, primaryUrl, primaryLabel, and optionally secondaryUrl/secondaryLabel. --}}
<section class="relative mb-14 overflow-hidden rounded-3xl bg-brand-900 bg-cover bg-center px-6 py-10 text-white sm:px-10 sm:py-12 dark:bg-brand-950" style="background-image: url('{{ asset('images/backgrounds/hero-dark.svg') }}')">
    <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
        <div class="max-w-2xl">
            @if(! empty($eyebrow))<p class="text-[11px] font-semibold tracking-[0.16em] text-brand-200 uppercase">{{ $eyebrow }}</p>@endif
            <h2 class="mt-3 text-3xl leading-tight font-bold tracking-tight sm:text-4xl">{{ $heading }}</h2>
            @if(! empty($body))<p class="mt-3 text-sm text-brand-100">{{ $body }}</p>@endif
        </div>
        <div class="flex shrink-0 flex-col items-start gap-3 sm:flex-row sm:items-center lg:flex-col lg:items-end">
            <x-ui.button :href="$primaryUrl" variant="white" size="lg">{{ $primaryLabel }} <span aria-hidden="true">→</span></x-ui.button>
            @if(! empty($secondaryUrl))<a href="{{ $secondaryUrl }}" class="text-sm font-medium text-brand-100 hover:text-white hover:underline">{{ $secondaryLabel }} <span aria-hidden="true">→</span></a>@endif
        </div>
    </div>
</section>
