{{--
    Mission / Vision opening: a pinned floating-island scroll story. Scrolling scrubs a fly-in video
    (cached frame by frame in resources/js/island-story.js) while the page's purpose copy from
    config/purpose.php plays out in four beats: heading → statement → why it matters → how we work.
--}}
@props(['page', 'purpose'])
@php
    $services = ['news' => __('News'), 'jobs' => __('Jobs'), 'shopping' => __('Shopping'), 'business' => __('Business'), 'education' => __('Education'), 'social' => __('Community'), 'hosting' => __('Hosting'), 'events' => __('Events')];
    $spots = [['left' => 12, 'top' => 62, 'flip' => false], ['left' => 58, 'top' => 70, 'flip' => false], ['left' => 82, 'top' => 46, 'flip' => true]];
    $cta = auth()->check()
        ? ['url' => route('dashboard'), 'label' => __('Go to your dashboard')]
        : ['url' => route('register'), 'label' => __('Join Cambo Hub')];
    $rocks = asset('images/island/island-rocks.webp');
@endphp
<div class="isl" data-island-story>
    <section class="isl-story" aria-labelledby="purpose-title">
        <div class="isl-stage">
            <div class="isl-cam" data-isl-cam>
                <canvas data-isl-canvas aria-hidden="true"></canvas>
                <img class="isl-plate" data-isl-plate src="{{ asset('images/island/island-hero.webp') }}" width="2400" height="1357" alt="" fetchpriority="high">
                <div class="isl-mist" aria-hidden="true"></div>
            </div>

            <video data-isl-video class="isl-video" muted playsinline preload="none" src="{{ asset('images/island/island-flyin.mp4') }}" aria-hidden="true"></video>

            <div class="isl-fog" data-isl-fog aria-hidden="true"><div class="f0"></div><div class="f1"></div><div class="f2"></div></div>

            {{-- Beat 1: the page heading, tucked behind the island's ferns. --}}
            <div class="isl-layer" data-isl-l0>
                <div class="isl-hero-copy" data-isl-hero>
                    <span class="isl-chip"><i class="isl-ping" aria-hidden="true"></i>{{ __($purpose['eyebrow']) }}</span>
                    <h1 id="purpose-title"><span class="isl-line"><span>{{ __($purpose['heading']) }} <em>{{ __($purpose['highlight']) }}</em></span></span></h1>
                </div>
            </div>

            <div class="isl-cam isl-camF" data-isl-camf aria-hidden="true">
                <img data-isl-cutout src="{{ asset('images/island/island-cutout.webp') }}" width="2400" height="1357" alt="">
            </div>

            <div class="isl-rock isl-rk1" data-isl-rock data-x="-.3" data-y="-.6" data-depth="1.3" aria-hidden="true"><div class="isl-bob"><img src="{{ $rocks }}" alt=""></div></div>
            <div class="isl-rock isl-rk3" data-isl-rock data-x="1" data-y=".55" data-depth="1.8" aria-hidden="true"><div class="isl-bob"><img src="{{ $rocks }}" alt=""></div></div>

            <div class="isl-layer" data-isl-l1>
                <div class="isl-hud" data-isl-hud aria-hidden="true">
                    <i class="isl-x"></i>
                    <span class="isl-chip isl-chip--sm">{{ __('Phnom Penh') }}</span>
                    <pre>LAT  11.5564° N
LON 104.9282° E
GMT +07:00</pre>
                    <i class="isl-frame"></i>
                </div>
                <div class="isl-intro" data-isl-fadeout>
                    <nav class="isl-switch" aria-label="{{ __('About Cambo Hub') }}">
                        @foreach(['mission' => 'Our mission', 'vision' => 'Our vision'] as $routeName => $label)
                            <a href="{{ route($routeName) }}" @if($page === $routeName) aria-current="page" @endif>{{ __($label) }}</a>
                        @endforeach
                    </nav>
                    <p>{{ __($purpose['intro']) }}</p>
                </div>
                <div class="isl-hint" data-isl-fadeout aria-hidden="true"><span class="isl-hint-c"><i></i></span>{{ __('Scroll to explore') }}</div>
            </div>

            {{-- Beat 2: the purpose statement resolves mid-flight. --}}
            <div class="isl-layer" data-isl-l2>
                <div class="isl-vision">
                    <span class="isl-chip" data-isl-vfade>{{ __($purpose['label']) }}</span>
                    <h2>
                        @foreach(preg_split('/\s+/u', __($purpose['statement'])) as $word)<span class="isl-w" data-isl-vword>{{ $word }}</span> @endforeach
                    </h2>
                    <p data-isl-vfade>{{ __($purpose['image_label']) }}</p>
                </div>
            </div>

            {{-- Beat 3: why it matters, with the three pillars pinned on the landscape. --}}
            <div class="isl-layer" data-isl-l3>
                <div class="isl-acct">
                    <span class="isl-chip">{{ __('See what drives us') }}</span>
                    <p data-isl-acct>{{ __($purpose['statement_body']) }}</p>
                </div>
                @foreach($purpose['pillars'] as $pillar)
                    @php($spot = $spots[$loop->index] ?? $spots[0])
                    <button type="button" class="isl-hs @if($spot['flip']) isl-hs--flip @endif" data-isl-hs style="left: {{ $spot['left'] }}%; top: {{ $spot['top'] }}%" aria-label="{{ __($pillar['title']) }}">
                        <i class="isl-dot" aria-hidden="true"><img src="{{ asset('images/icons/'.$pillar['icon'].'-blue.svg') }}" width="14" height="14" alt=""></i>
                        <span class="isl-lab">{{ __($pillar['title']) }}<em>{{ __($pillar['body']) }}</em></span>
                    </button>
                @endforeach
            </div>

            {{-- Beat 4: how we work. --}}
            <div class="isl-layer isl-l4" data-isl-l4>
                <h2 class="isl-cred" data-isl-cred><span class="isl-chip">{{ __($purpose['story_label']) }}</span>{{ __($purpose['story_heading']) }}</h2>
                <a class="isl-card" data-isl-card href="{{ $cta['url'] }}">
                    <span class="isl-up">{{ __('Free') }}</span>
                    <h3>{{ __('Everyday services') }}<br>{{ __('in one account') }}</h3>
                    <div class="isl-big"><b data-isl-cnum data-to="{{ count($services) }}">0</b><span>{{ __('services') }}</span></div>
                    <ul class="isl-icons">
                        @foreach($services as $icon => $name)
                            <li data-isl-icon title="{{ $name }}"><img src="{{ asset('images/icons/'.$icon.'-blue.svg') }}" width="18" height="18" alt="{{ $name }}"></li>
                        @endforeach
                    </ul>
                    <span class="isl-card-cta">{{ $cta['label'] }} <span aria-hidden="true">→</span></span>
                </a>
                <ol class="isl-feats">
                    @foreach($purpose['principles'] as $principle)
                        <li data-isl-row>
                            <span class="isl-num">0{{ $loop->iteration }}</span>
                            <div><strong>{{ __($principle['title']) }}</strong><p>{{ __($principle['body']) }}</p></div>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="isl-prog" aria-hidden="true"><i data-isl-prog></i></div>
        </div>
    </section>
</div>
