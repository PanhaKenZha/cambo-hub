{{--
    "Built around everyday life": nine people on a turning ring (drag, arrow keys or autoplay).
    Open a person to see the Cambo Hub services in their day — numbered dots on the figure, and a
    receipt-style card listing each service with a link. Behaviour: resources/js/people-ring.js.
--}}
@php
    $search = fn (string $q): string => route('search', ['q' => $q]);
    $item = fn (string $name, string $kind, string $icon, string $url): array => compact('name', 'kind', 'icon', 'url');
    // [name, foot offset, dot positions (% of figure), services]
    $people = [
        [__('First Step'), .0501, [[52, 44], [80, 40], [44, 57]], [
            $item(__('Short courses'), __('Education'), 'education', $search('courses')),
            $item(__('Internships'), __('Jobs'), 'jobs', route('jobs.index')),
            $item(__('Campus news'), __('News'), 'news', route('news.index')),
        ]],
        [__('Phone Interview'), .0501, [[76, 45], [54, 68], [45, 87]], [
            $item(__('Open jobs'), __('Jobs'), 'jobs', route('jobs.index')),
            $item(__('Companies hiring'), __('Business'), 'business', route('jobs.index')),
            $item(__('Your profile'), __('Account'), 'profile', route('register')),
        ]],
        [__('New In Town'), .0656, [[66, 47], [51, 64], [48, 87]], [
            $item(__('Local businesses'), __('Business'), 'business', $search('business')),
            $item(__('Things to do'), __('Events'), 'events', $search('events')),
            $item(__('Neighbourhood news'), __('News'), 'news', route('news.index')),
        ]],
        [__('Creator'), .0582, [[76, 47], [43, 70], [54, 88]], [
            $item(__('Share your story'), __('Community'), 'social', $search('community')),
            $item(__('Sell online'), __('Shopping'), 'shopping', $search('shopping')),
            $item(__('Reach new people'), __('Advertising'), 'advertising', route('contact')),
        ]],
        [__('Letters Home'), .065, [[72, 41], [51, 68], [45, 89]], [
            $item(__('Today’s headlines'), __('News'), 'news', route('news.index')),
            $item(__('Messages'), __('Community'), 'messages', route('register')),
            $item(__('Local stories'), __('Community'), 'community', route('news.index')),
        ]],
        [__('Career Switch'), .047, [[75, 45], [51, 70], [46, 88]], [
            $item(__('New directions'), __('Jobs'), 'jobs', route('jobs.index')),
            $item(__('Build new skills'), __('Education'), 'education', $search('courses')),
            $item(__('Freelance work'), __('Freelance'), 'freelance', $search('freelance')),
        ]],
        [__('Weekend Plans'), .0377, [[69, 43], [51, 72], [56, 92]], [
            $item(__('Concerts & meetups'), __('Events'), 'events', $search('events')),
            $item(__('What’s on'), __('News'), 'news', route('news.index')),
            $item(__('Local makers'), __('Shopping'), 'shopping', $search('shopping')),
        ]],
        [__('Hosting Guests'), .0891, [[65, 47], [43, 68], [60, 86]], [
            $item(__('Places to stay'), __('Hosting'), 'hosting', $search('hosting')),
            $item(__('Getting around'), __('Transport'), 'vehicles', $search('transport')),
            $item(__('Festivals'), __('Events'), 'events', $search('events')),
        ]],
        [__('Shop Local'), .0588, [[66, 41], [48, 72], [56, 88]], [
            $item(__('Business directory'), __('Business'), 'business', $search('business')),
            $item(__('Markets & sellers'), __('Shopping'), 'shopping', $search('shopping')),
            $item(__('Home delivery'), __('Delivery'), 'delivery', $search('delivery')),
        ]],
    ];
    $looks = collect($people)->values()->map(fn (array $p, int $i): array => [
        'name' => $p[0], 'foot' => $p[1], 'tags' => $p[2],
        'img' => asset('images/people/person-0'.($i + 1).'.webp'),
        'items' => collect($p[3])->map(fn (array $it): array => $it + ['iconUrl' => asset('images/icons/'.$it['icon'].'-blue.svg')])->all(),
    ])->all();
    $cta = auth()->check()
        ? ['url' => route('dashboard'), 'label' => __('Go to your dashboard')]
        : ['url' => route('register'), 'label' => __('Join Cambo Hub')];
    $strings = [
        'services' => __('services'), 'person' => __('Person'), 'open' => __('Open'),
        'cta' => $cta['label'], 'look' => __('Person'),
    ];
@endphp
<div class="pr" data-people-ring>
    <script type="application/json" data-pr-data>@json(['looks' => $looks, 'cta' => $cta, 't' => $strings])</script>
    <section class="deck" id="pr-deck" aria-labelledby="pr-title">
        <div class="deck__intro">
            <h2 id="pr-title">{{ __('Different needs. One familiar place.') }}</h2>
            <p>{{ __('Nine people, one Cambo Hub. Turn the ring, choose a person, and tap a dot to explore the services in their day.') }}</p>
        </div>

        <div class="deck__stage" id="pr-stage" tabindex="0" role="region" aria-roledescription="carousel" aria-label="{{ __('People — arrow keys turn the ring, Enter opens a person') }}">
            <span class="loop__ring" id="pr-loopRing"></span>
            <div class="loop" aria-hidden="true">
                <span class="loop__eyebrow">{{ __('Designed around everyday life') }}</span>
                <p class="loop__t">{{ __('Different needs.') }}<br><span>{{ __('One familiar place.') }}</span></p>
                <p class="loop__s">{{ __('Nine people — one Cambo Hub') }}</p>
            </div>
            <div class="deck__crowd" id="pr-crowd"></div>
            <div class="deck__labels" id="pr-labels"></div>
            <span class="deck__glint"></span>
            <span class="follow" id="pr-follow"><span class="follow__in"><b class="f-drag">{{ __('Drag') }}</b><b class="f-view">{{ __('View') }}</b></span></span>
            <span class="deck__live" id="pr-live" aria-live="polite"></span>
        </div>

        <div class="dock">
            <div class="dock__now" id="pr-now">
                <div class="dock__count"><span class="cap"><span id="pr-nowIdx">01</span> / <span id="pr-nowTotal">09</span></span><button type="button" class="dock__auto" id="pr-auto" aria-label="{{ __('Autoplay') }}" aria-pressed="true"></button></div>
                <span class="dock__name" id="pr-nowName">{{ $looks[0]['name'] }}</span>
            </div>
            <ul class="dock__pieces" id="pr-pieces" aria-label="{{ __('Services in their day') }}"></ul>
            <button type="button" class="dock__shop" id="pr-shop"><span class="cap">{{ __('See their day') }}</span><span class="dock__dot" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M12 4v16M4 12h16"/></svg></span></button>
        </div>

        <button type="button" class="deck__looksbtn pill" id="pr-drawerBtn" aria-expanded="false" aria-controls="pr-drawer">{{ __('People') }}</button>
        <div class="deck__view" role="group" aria-label="{{ __('View') }}">
            <button type="button" class="deck__vbtn" data-v="ring" aria-pressed="true">{{ __('Ring') }}</button>
            <button type="button" class="deck__vbtn" data-v="grid" aria-pressed="false">{{ __('Grid') }}</button>
        </div>

        <div class="drawer" id="pr-drawer" aria-hidden="true">
            <div class="drawer__top"><span class="cap">{{ __('The people we build for') }}</span><button type="button" class="pill" id="pr-drawerClose">{{ __('Close') }}</button></div>
            <ol class="drawer__list" id="pr-drawerList"></ol>
            <nav class="drawer__nav" aria-label="{{ __('Explore Cambo Hub') }}">
                <a class="pill" href="{{ route('mission') }}">{{ __('Our mission') }}</a>
                <a class="pill" href="{{ route('vision') }}">{{ __('Our vision') }}</a>
                <a class="pill" href="{{ route('jobs.index') }}">{{ __('Jobs') }}</a>
                <a class="pill" href="{{ route('news.index') }}">{{ __('News') }}</a>
            </nav>
        </div>

        <div class="focus" id="pr-focus" aria-hidden="true">
            <div class="focus__stage" id="pr-focusStage"></div>
            <div class="focus__top">
                <p class="focus__cap"><span id="pr-focusNum">01.</span><i id="pr-focusName">{{ $looks[0]['name'] }}</i></p>
                <button type="button" class="focus__close" id="pr-focusClose" aria-label="{{ __('Close') }}"><svg viewBox="0 0 24 24" fill="none"><path d="M5 5l14 14M19 5L5 19"/></svg></button>
            </div>
            <p class="focus__hint cap">{{ __('Tap a dot to explore the service') }}</p>
        </div>

        <aside class="slip" id="pr-slip" aria-label="{{ __('A day on Cambo Hub') }}" aria-hidden="true">
            <div class="slip__head">
                <div class="slip__logo">Cambo Hub</div>
                <div class="cap">{{ __('A day on Cambo Hub') }} · {{ __('Phnom Penh') }}</div>
                <div class="cap" id="pr-slipLook"></div>
            </div>
            <ul class="slip__lines" id="pr-lines" role="list"></ul>
            <div class="slip__bundle">
                <div class="sum__total"><span>{{ __('3 services') }}</span><span>{{ __('1 account') }}</span></div>
                <a class="slip__cta" href="{{ $cta['url'] }}"><span>{{ $cta['label'] }}</span><svg viewBox="0 0 26 14" fill="none" aria-hidden="true"><path d="M1 7h24M19 1l6 6-6 6"/></svg></a>
            </div>
            <div class="slip__foot" aria-hidden="true"><span class="slip__code"></span><span class="cap">{{ __('One account. Everyday possibilities.') }}</span></div>
        </aside>
    </section>
</div>
