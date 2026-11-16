{{--
    The plant scene shares the hero's pinned stage: after the last field frame the view darkens,
    the plant rises out of the same field, and its roots are revealed beneath the ground line.
--}}
<div class="scene-growth" aria-hidden="true">
    <img class="scene-depth" src="{{ asset('plant-field-depth-blurred.webp') }}" width="1920" height="1080" alt="" loading="lazy">
    <div class="scene-shade"></div>
    {{-- Pollen drifting through the air; positions and timings are fixed so the layout never shifts. --}}
    <div class="scene-air">
        @for($i = 0; $i < 18; $i++)
            <span style="--x: {{ ($i * 37 + 11) % 100 }}%; --size: {{ 2 + ($i * 7) % 4 }}px; --duration: {{ 9 + ($i * 5) % 8 }}s; --delay: -{{ ($i * 13) % 17 }}s; --sway: {{ ($i % 2 ? 1 : -1) * (20 + ($i * 11) % 40) }}px"></span>
        @endfor
    </div>
    <div class="growth-specimen">
        <div class="growth-crown">
            <img src="{{ asset('plant-top.webp') }}" width="2520" height="3030" alt="" loading="lazy">
            <img class="growth-normal" src="{{ asset('plant-top-normal.webp') }}" width="2520" height="3030" alt="" loading="lazy">
            <span class="growth-crown-light" style="--crown-depth: url('{{ asset('plant-top-depth-20px.webp') }}')"></span>
        </div>
        <div class="growth-roots">
            <img src="{{ asset('plant-bottom.webp') }}" width="2520" height="1400" alt="" loading="lazy">
            <img class="growth-root-outline" src="{{ asset('plant-bottom-outline-alpha.webp') }}" width="2520" height="1400" alt="" loading="lazy">
            <span class="growth-root-light" style="--root-mask: url('{{ asset('plant-bottom-roots-mask.webp') }}')"></span>
            <span class="growth-root-glow" style="--glow-mask: url('{{ asset('plant-bottom-glow-mask.webp') }}')"></span>
        </div>
    </div>
</div>
<div id="growth-animation" class="scene-chapters" aria-label="{{ __('A connected journey') }}">
    <article class="growth-chapter" data-scene-chapter>
        <span class="nature-label">{{ __('01 — The possibilities') }}</span>
        <h2>{{ __('Room to grow.') }}</h2>
        <p>{{ __('Discover the people, places, and opportunities that make everyday life in Cambodia more rewarding.') }}</p>
        <a href="{{ route('search') }}">{{ __('Explore Cambo Hub') }} <span aria-hidden="true">↗</span></a>
    </article>
    <article class="growth-chapter" data-scene-chapter>
        <span class="nature-label">{{ __('02 — The connections') }}</span>
        <h2>{{ __('Small beginnings.') }}<br>{{ __('Bigger possibilities.') }}</h2>
        <p>{{ __('Build skills, discover local businesses, and find your next opportunity. Every connection can be the beginning of something more.') }}</p>
        <a href="{{ route('mission') }}">{{ __('Discover our purpose') }} <span aria-hidden="true">↗</span></a>
    </article>
    <article class="growth-chapter" data-scene-chapter>
        <span class="nature-label">{{ __('03 — The foundation') }}</span>
        <h2>{{ __('Connected.') }}<br>{{ __('From the ground up.') }}</h2>
        <p>{{ __('One place to bring your everyday activities together, with your community at the heart of it.') }}</p>
        <a href="{{ route('contact') }}">{{ __('Grow with us') }} <span aria-hidden="true">↗</span></a>
    </article>
</div>
<div class="scene-index" aria-hidden="true"><span>{{ __('The connected experience') }}</span><span data-growth-index>01 / 03</span></div>
