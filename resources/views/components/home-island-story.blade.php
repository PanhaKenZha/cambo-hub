{{--
    Floating-island scroll story: a pinned stage where scrolling scrubs a fly-in video (cached frame by frame
    in resources/js/island-story.js), followed by a services panel that slides up over the end of the stage.
    The copy follows the Cambo Hub mission and vision (config/purpose.php).
--}}
@props(['stats' => [], 'registrationOpen' => true])
@php
    $byIcon = collect($stats)->keyBy('icon');
    $statValue = fn (string $icon, string $suffix, string $fallback): string => $byIcon->has($icon)
        ? number_format($byIcon[$icon]['value']).' '.$suffix
        : $fallback;
    $hotspots = [
        ['left' => 12, 'top' => 58, 'label' => __('Jobs'), 'value' => $statValue('jobs', __('open now'), __('Your next step'))],
        ['left' => 33, 'top' => 41, 'label' => __('Local business'), 'value' => $statValue('business', __('hiring'), __('Shop local'))],
        ['left' => 70, 'top' => 66, 'label' => __('Learning'), 'value' => __('Build new skills')],
        ['left' => 83, 'top' => 47, 'label' => __('Community'), 'value' => $statValue('news', __('stories'), __('Share your story'))],
    ];
    $services = ['news' => __('News'), 'jobs' => __('Jobs'), 'shopping' => __('Shopping'), 'business' => __('Business'), 'education' => __('Education'), 'social' => __('Community'), 'hosting' => __('Hosting'), 'events' => __('Events')];
    $features = [
        ['label' => __('Open doors to opportunity'), 'url' => route('jobs.index')],
        ['label' => __('Help local ambition grow'), 'url' => route('search')],
        ['label' => __('Bring people together'), 'url' => route('news.index')],
        ['label' => __('Built in Khmer and English'), 'url' => route('mission')],
        ['label' => __('Trusted, secure, one account'), 'url' => route('vision')],
    ];
    $cards = [
        ['image' => 'images/genomines/plant-nursery.webp', 'position' => '50% 60%', 'tag' => __('Jobs & learning'), 'title' => [__('Open doors'), __('to opportunity')], 'body' => __('Discover jobs, courses and services that support your next step — from a first role to a new direction.'), 'url' => route('jobs.index')],
        ['image' => 'images/island/island-clouds.webp', 'position' => '50% 50%', 'tag' => __('Local business'), 'title' => [__('Help local'), __('ambition grow')], 'body' => __('Cambodian businesses, sellers and creators get a place to be discovered by the people who need them.'), 'url' => route('search')],
        ['image' => 'images/genomines/seedling-rows.webp', 'position' => '50% 40%', 'tag' => __('News & community'), 'title' => [__('Bring people'), __('together')], 'body' => __('Everyday news, community conversations and useful services in one consistent experience.'), 'url' => route('news.index')],
    ];
    $cta = auth()->check()
        ? ['url' => route('dashboard'), 'label' => __('Go to your dashboard')]
        : ($registrationOpen ? ['url' => route('register'), 'label' => __('Join Cambo Hub')] : ['url' => route('login'), 'label' => __('Sign in')]);
    $rocks = asset('images/island/island-rocks.webp');
@endphp
<div class="isl" data-island-story>
    <section class="isl-story" aria-label="{{ __('Cambodia, closer together') }}">
        <div class="isl-stage">
            <div class="isl-cam" data-isl-cam>
                <canvas data-isl-canvas aria-hidden="true"></canvas>
                <img class="isl-plate" data-isl-plate src="{{ asset('images/island/island-hero.webp') }}" width="2400" height="1357" alt="" loading="lazy">
                <div class="isl-mist" aria-hidden="true"></div>
            </div>

            <video data-isl-video class="isl-video" muted playsinline preload="none" src="{{ asset('images/island/island-flyin.mp4') }}" aria-hidden="true"></video>

            <div class="isl-fog" data-isl-fog aria-hidden="true"><div class="f0"></div><div class="f1"></div><div class="f2"></div></div>

            <div class="isl-layer" data-isl-l0>
                <div class="isl-hero-copy" data-isl-hero>
                    <span class="isl-chip"><i class="isl-ping" aria-hidden="true"></i><span lang="km" class="font-khmer">កម្ពុជា</span><b aria-hidden="true"></b>{{ __('Cambodia, closer together') }}</span>
                    <h2><span class="isl-line"><span>{{ __('More opportunity.') }} <em>{{ __('Less distance.') }}</em></span></span></h2>
                </div>
            </div>

            <div class="isl-cam isl-camF" data-isl-camf aria-hidden="true">
                <img data-isl-cutout src="{{ asset('images/island/island-cutout.webp') }}" width="2400" height="1357" alt="" loading="lazy">
            </div>

            <div class="isl-rock isl-rk1" data-isl-rock data-x="-.3" data-y="-.6" data-depth="1.3" aria-hidden="true"><div class="isl-bob"><img src="{{ $rocks }}" alt="" loading="lazy"></div></div>
            <div class="isl-rock isl-rk3" data-isl-rock data-x="1" data-y=".55" data-depth="1.8" aria-hidden="true"><div class="isl-bob"><img src="{{ $rocks }}" alt="" loading="lazy"></div></div>

            <div class="isl-layer" data-isl-l1>
                <div class="isl-hud" data-isl-hud aria-hidden="true">
                    <i class="isl-x"></i>
                    <span class="isl-chip">{{ __('Phnom Penh') }}</span>
                    <pre>LAT  11.5564° N
LON 104.9282° E
GMT +07:00</pre>
                    <i class="isl-frame"></i>
                </div>
                <p class="isl-caption" data-isl-caption>{{ __('One account.') }}<br>{{ __('Everyday possibilities.') }}<br>{{ __('Built for Cambodia.') }}</p>
                <div class="isl-hint" data-isl-hint aria-hidden="true"><span class="isl-hint-c"><i></i></span>{{ __('Scroll to explore') }}</div>
            </div>

            <div class="isl-layer" data-isl-l2>
                <div class="isl-vision">
                    <span class="isl-chip" data-isl-vfade>{{ __('Our vision') }}</span>
                    <h3>
                        @foreach([__('A connected Cambodia.'), __('A shared future.')] as $line)
                            @foreach(preg_split('/\s+/u', $line) as $word)<span class="isl-w" data-isl-vword>{{ $word }}</span> @endforeach
                            @unless($loop->last)<br>@endunless
                        @endforeach
                    </h3>
                    <p data-isl-vfade>{{ __('Where people, businesses and communities take part in digital life with confidence —') }}<br>{{ __('and find more possibilities close to home.') }}</p>
                </div>
            </div>

            <div class="isl-layer" data-isl-l3>
                <div class="isl-acct">
                    <span class="isl-chip">{{ __('Our mission') }}</span>
                    <p data-isl-acct>{{ __('Finding a job, supporting a local shop, learning a skill or sharing a story should feel like parts of the same everyday life. Cambo Hub brings them together.') }}</p>
                </div>
                @foreach($hotspots as $spot)
                    <button type="button" class="isl-hs" data-isl-hs style="left: {{ $spot['left'] }}%; top: {{ $spot['top'] }}%">
                        <i class="isl-dot" aria-hidden="true"></i>
                        <span class="isl-lab">{{ $spot['label'] }}<em>{{ $spot['value'] }}</em></span>
                    </button>
                @endforeach
            </div>

            <div class="isl-layer isl-l4" data-isl-l4>
                <h3 class="isl-cred" data-isl-cred><span class="isl-tag">✕ {{ __('One account') }}</span>{{ __('One place to connect.') }}<br>{{ __('More ways to move forward.') }}</h3>
                <a class="isl-card" data-isl-card href="{{ $cta['url'] }}">
                    <h4>{{ __('Everyday services') }}<br>{{ __('in one account') }}</h4>
                    <div class="isl-note">{{ __('Khmer · English · one login') }}</div>
                    <div class="isl-big"><b data-isl-cnum data-to="{{ count($services) }}">0</b><span>{{ __('services') }}</span></div>
                    <span class="isl-up">{{ __('Free') }}</span>
                    <ul class="isl-icons">
                        @foreach($services as $icon => $name)
                            <li data-isl-icon title="{{ $name }}"><img src="{{ asset('images/icons/'.$icon.'-blue.svg') }}" width="18" height="18" alt="{{ $name }}" loading="lazy"></li>
                        @endforeach
                    </ul>
                    <div class="isl-q">{{ $cta['label'] }} →</div>
                </a>
                <ul class="isl-feats" data-isl-feats>
                    @foreach($features as $feature)
                        <li data-isl-row><a href="{{ $feature['url'] }}"><span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><span>{{ $feature['label'] }}</span><span aria-hidden="true">→</span></a></li>
                    @endforeach
                </ul>
            </div>

            <div class="isl-prog" aria-hidden="true"><i data-isl-prog></i></div>
        </div>
    </section>

    <section class="isl-sol" aria-labelledby="isl-sol-title">
        <div class="isl-sol-inner">
            <div class="isl-sol-head">
                <div>
                    <span class="isl-chip">{{ __('Many possibilities. One Cambo Hub.') }}</span>
                    <h2 id="isl-sol-title">{{ __('A purpose that connects every service.') }}</h2>
                </div>
                <p>{{ __('We are building more than one screen of services. We are making them work together, so a new connection can become a new skill, a new customer, or a new beginning.') }}</p>
            </div>
            <div class="isl-cards">
                @foreach($cards as $card)
                    <a class="isl-scard" data-isl-tilt href="{{ $card['url'] }}">
                        <div class="isl-bg" style="background-image: url('{{ asset($card['image']) }}'); background-position: {{ $card['position'] }}"></div>
                        <div class="isl-ov"></div>
                        <div class="isl-top"><span>0{{ $loop->iteration }} — {{ $card['tag'] }}</span><span class="isl-arr" aria-hidden="true">→</span></div>
                        <div class="isl-bot"><h3>{{ $card['title'][0] }}<br>{{ $card['title'][1] }}</h3><p>{{ $card['body'] }}</p></div>
                    </a>
                @endforeach
            </div>
            <div class="isl-cta">
                <div>
                    <span class="isl-cta-eyebrow">{{ __('Let’s move forward, together') }}</span>
                    <h3>{{ __('Your next chapter') }} <span>{{ __('belongs here.') }}</span></h3>
                </div>
                <div class="isl-cta-actions">
                    <x-ui.button :href="$cta['url']" size="lg" class="group">{{ $cta['label'] }} <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span></x-ui.button>
                    <a href="{{ route('mission') }}" class="isl-cta-link">{{ __('Discover our purpose') }} ↗</a>
                </div>
            </div>
        </div>
    </section>
</div>
