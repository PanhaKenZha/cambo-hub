<section class="grid gap-5 border-t border-line py-10 md:grid-cols-2" aria-label="{{ __($section->heading) }}">
    @foreach($items as $item)
        <article class="group relative isolate flex min-h-56 items-end overflow-hidden rounded-3xl bg-brand-900 text-white shadow-card">
            @if($item->imageUrl())
                <img src="{{ $item->imageUrl() }}" alt="" width="640" height="320" loading="lazy" class="absolute inset-0 -z-20 size-full object-cover transition duration-500 group-hover:scale-105 motion-reduce:transition-none">
            @endif
            <div class="absolute inset-0 -z-10 bg-gradient-to-r from-brand-950/95 via-brand-950/70 to-brand-950/10" aria-hidden="true"></div>
            <div class="max-w-md p-6 sm:p-8">
                @if($item->badge)<x-ui.badge variant="solid">{{ $item->badge }}</x-ui.badge>@endif
                <h3 class="mt-3 text-xl leading-snug font-bold sm:text-2xl">{{ $item->title }}</h3>
                @if($item->summary)<p class="mt-2 text-sm text-brand-100">{{ $item->summary }}</p>@endif
                @if($item->linkUrl())
                    <x-ui.button :href="$item->linkUrl()" variant="white" size="sm" class="stretched-link mt-5">{{ $item->detail('cta_label', __('Learn more')) }} <span aria-hidden="true">→</span></x-ui.button>
                @endif
            </div>
        </article>
    @endforeach
</section>
