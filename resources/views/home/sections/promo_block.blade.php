{{-- Promotional block from Website › Homepage: heading, description, button and optional background image. --}}
<section class="relative my-10 overflow-hidden rounded-3xl bg-brand-900 bg-cover bg-center px-6 py-10 text-white sm:px-10 sm:py-12 dark:bg-brand-950" style="background-image: url('{{ $section['image_url'] ?? asset('images/backgrounds/hero-dark.svg') }}')" aria-labelledby="promo-{{ $loop->index }}">
    @if($section['image_url'])<div class="absolute inset-0 bg-gradient-to-r from-brand-950/95 via-brand-950/75 to-brand-950/30" aria-hidden="true"></div>@endif
    <div class="relative flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
        <div class="max-w-2xl">
            <h2 id="promo-{{ $loop->index }}" class="text-3xl leading-tight font-bold tracking-tight sm:text-4xl">{{ __($section['title']) }}</h2>
            @if(filled($section['subtitle']))<p class="mt-3 text-sm text-brand-100">{{ __($section['subtitle']) }}</p>@endif
        </div>
        @if(filled($section['link_url']))
            <div class="flex shrink-0 flex-col items-start gap-3 sm:flex-row sm:items-center lg:flex-col lg:items-end">
                @php($isSignUpLink = rtrim(url($section['link_url']), '/') === route('register'))
                @if($isSignUpLink && auth()->check())
                    <x-ui.button :href="route('dashboard')" variant="white" size="lg">{{ __('Go to your dashboard') }} <span aria-hidden="true">→</span></x-ui.button>
                @elseif(! $isSignUpLink || $registrationOpen)
                    <x-ui.button :href="$section['link_url']" variant="white" size="lg">{{ filled($section['button_label']) ? __($section['button_label']) : __('Learn more') }} <span aria-hidden="true">→</span></x-ui.button>
                    @if($isSignUpLink)<a href="{{ route('login') }}" class="text-sm font-medium text-brand-100 hover:text-white hover:underline">{{ __('I already have an account') }} <span aria-hidden="true">→</span></a>@endif
                @endif
            </div>
        @endif
    </div>
</section>
