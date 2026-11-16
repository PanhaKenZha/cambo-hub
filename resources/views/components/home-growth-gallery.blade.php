<section class="growth-gallery" aria-labelledby="growth-gallery-heading">
    <header data-reveal>
        <span class="nature-label">{{ __('A world of possibility') }}</span>
        <h2 id="growth-gallery-heading">{{ __('Small beginnings.') }}<br><span>{{ __('Extraordinary potential.') }}</span></h2>
        <p>{{ __('A little curiosity. A new connection. A different perspective. There is always more to discover.') }}</p>
    </header>
    <div class="growth-gallery-grid">
        <figure class="growth-gallery-nursery" data-reveal>
            <div><img src="{{ asset('images/genomines/plant-nursery.webp') }}" width="960" height="1280" alt="{{ __('Young plants growing under the shade of a nursery') }}" loading="lazy"></div>
            <figcaption><span>01</span>{{ __('Room to grow.') }}</figcaption>
        </figure>
        <figure class="growth-gallery-crystals" data-reveal>
            <div><img src="{{ asset('images/genomines/nickel-crystals.webp') }}" width="1158" height="776" alt="{{ __('Turquoise mineral crystals catching the light') }}" loading="lazy"></div>
            <figcaption><span>02</span>{{ __('A different perspective.') }}</figcaption>
        </figure>
        <figure class="growth-gallery-seedlings" data-reveal>
            <div><img src="{{ asset('images/genomines/seedling-rows.webp') }}" width="960" height="1280" alt="{{ __('Orderly rows of seedlings growing in individual pots') }}" loading="lazy"></div>
            <figcaption><span>03</span>{{ __('Progress takes root.') }}</figcaption>
        </figure>
        <figure class="growth-gallery-energy" data-reveal>
            <div><img src="{{ asset('images/genomines/battery-pack.webp') }}" width="1536" height="746" alt="{{ __('Details of a modern electric battery assembly') }}" loading="lazy"></div>
            <figcaption><span>04</span>{{ __('Energy for what comes next.') }}</figcaption>
        </figure>
    </div>
    <div class="growth-gallery-closing" data-reveal>
        <p>{{ __('Your next possibility') }}<br><span>{{ __('starts with a connection.') }}</span></p>
        <a href="{{ route('search') }}">{{ __('Find your next discovery') }} <span aria-hidden="true">↗</span></a>
    </div>
</section>
