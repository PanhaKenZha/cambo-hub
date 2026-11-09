{{-- Banners from Website › Banners (homepage placement). $items are SiteContent::banners() rows. --}}
<section @class(['grid gap-5 border-t border-line py-10', 'md:grid-cols-2' => $items->count() > 1]) aria-label="{{ __($section['title']) }}">
    @foreach($items as $banner)
        <article class="group relative isolate flex min-h-56 items-end overflow-hidden rounded-3xl bg-brand-900 text-white shadow-card">
            @if($banner['image_url'])
                <img src="{{ $banner['image_url'] }}" alt="" width="640" height="320" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif class="absolute inset-0 -z-20 size-full object-cover transition duration-500 group-hover:scale-105 motion-reduce:transition-none">
            @endif
            <div class="absolute inset-0 -z-10 bg-gradient-to-r from-brand-950/95 via-brand-950/70 to-brand-950/10" aria-hidden="true"></div>
            <div class="max-w-md p-6 sm:p-8">
                <h3 class="text-xl leading-snug font-bold sm:text-2xl">{{ __($banner['title']) }}</h3>
                @if(filled($banner['subtitle']))<p class="mt-2 text-sm text-brand-100">{{ __($banner['subtitle']) }}</p>@endif
                @if(filled($banner['link_url']))
                    <x-ui.button :href="$banner['link_url']" variant="white" size="sm" class="stretched-link mt-5">{{ filled($banner['button_label']) ? __($banner['button_label']) : __('Learn more') }} <span aria-hidden="true">→</span></x-ui.button>
                @endif
            </div>
        </article>
    @endforeach
</section>
