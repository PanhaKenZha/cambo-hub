/*
 * Floating-island scroll story (resources/views/components/purpose-story.blade.php).
 *
 * The fly-in clip has very few keyframes, so it is never seeked live. It plays once (muted, offscreen)
 * while every frame is cached as a JPEG blob; the canvas then draws the cached frame that matches the
 * scroll position. One rAF loop drives every layer and only runs while the story is near the viewport.
 */

const FPS = 24;
const clamp = (v, a = 0, b = 1) => Math.min(b, Math.max(a, v));
const seg = (p, a, b) => clamp((p - a) / (b - a));
const ease = (t) => (t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2);
const setVisible = (el, on) => {
    const v = on ? 'visible' : 'hidden';
    if (el && el.style.visibility !== v) {
        el.style.visibility = v;
    }
};

function frameCache(video, canvas) {
    const ctx = canvas.getContext('2d');
    const grab = document.createElement('canvas');
    grab.width = 1600;
    grab.height = 900;
    const gctx = grab.getContext('2d');
    const state = { NF: 0, frames: [], cached: 0, want: 0, shown: -1, busy: false, redraw: false };

    const nearest = (t) => {
        for (let k = 0; k < state.NF; k++) {
            if (t - k >= 0 && state.frames[t - k]) return t - k;
            if (t + k < state.NF && state.frames[t + k]) return t + k;
        }
        return -1;
    };

    const drawWant = async () => {
        if (state.busy) return;
        const n = nearest(state.want);
        if (n < 0 || (n === state.shown && !state.redraw)) return;
        state.busy = true;
        try {
            const bm = await createImageBitmap(state.frames[n]);
            const cw = canvas.width;
            const ch = canvas.height;
            const s = Math.max(cw / bm.width, ch / bm.height);
            const w = bm.width * s;
            const h = bm.height * s;
            ctx.drawImage(bm, (cw - w) / 2, (ch - h) / 2, w, h);
            state.shown = n;
            state.redraw = false;
            bm.close();
        } catch {
            /* Decode failed: keep the last frame. */
        }
        state.busy = false;
        if (nearest(state.want) !== state.shown || state.redraw) drawWant();
    };

    const store = (i, quality) => {
        try {
            gctx.drawImage(video, 0, 0, 1600, 900);
            grab.toBlob((blob) => {
                if (!blob || state.frames[i]) return;
                state.frames[i] = blob;
                state.cached++;
                if (i === state.want || state.shown < 0 || Math.abs(i - state.want) < Math.abs(state.shown - state.want)) drawWant();
            }, 'image/jpeg', quality);
        } catch {
            /* Canvas unavailable: the high-res still stays in place. */
        }
    };

    /* Seek to each missing frame in turn (fallback, and to fill frames dropped during playback). */
    const seekFill = () => {
        const todo = [];
        for (let i = 0; i < state.NF; i++) if (!state.frames[i]) todo.push(i);
        if (!todo.length) return;
        let j = 0;
        const step = () => { video.currentTime = Math.min(todo[j] / FPS, video.duration - 0.01); };
        const onSeeked = () => {
            store(todo[j], todo[j] === 0 ? 0.9 : 0.86);
            if (++j < todo.length) step();
            else video.removeEventListener('seeked', onSeeked);
        };
        video.addEventListener('seeked', onSeeked);
        step();
    };

    const init = () => {
        if (state.NF) return;
        state.NF = Math.max(2, Math.round(video.duration * FPS));
        state.frames = new Array(state.NF);
        store(0, 0.9); // frame 0 first: the hero hand-off depends on it
        if ('requestVideoFrameCallback' in HTMLVideoElement.prototype) {
            const onFrame = (now, meta) => {
                const i = Math.min(state.NF - 1, Math.round(meta.mediaTime * FPS));
                if (i > 0 && !state.frames[i]) store(i, 0.86);
                if (!video.ended) video.requestVideoFrameCallback(onFrame);
            };
            video.requestVideoFrameCallback(onFrame);
            video.addEventListener('ended', () => setTimeout(seekFill, 50), { once: true });
            video.play().catch(seekFill);
        } else {
            seekFill();
        }
    };

    const load = () => {
        if (video.readyState >= 2) {
            init();
            return;
        }
        video.addEventListener('loadeddata', init, { once: true });
        video.preload = 'auto';
        video.load();
    };

    const resize = () => {
        const ratio = Math.min(window.devicePixelRatio || 1, 1.5);
        canvas.width = Math.round(canvas.clientWidth * ratio);
        canvas.height = Math.round(canvas.clientHeight * ratio);
        state.redraw = true;
        drawWant();
    };

    return { state, load, resize, drawWant };
}

export function initIslandStory() {
    const root = document.querySelector('[data-island-story]');
    if (!root) return;

    const q = (s) => root.querySelector(s);
    const qa = (s) => [...root.querySelectorAll(s)];
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');

    const story = q('.isl-story');
    const cams = qa('[data-isl-cam], [data-isl-camf]');
    const camF = q('[data-isl-camf]');
    const plate = q('[data-isl-plate]');
    const cutout = q('[data-isl-cutout]');
    const L0 = q('[data-isl-l0]');
    const L1 = q('[data-isl-l1]');
    const L2 = q('[data-isl-l2]');
    const L3 = q('[data-isl-l3]');
    const L4 = q('[data-isl-l4]');
    const heroCopy = q('[data-isl-hero]');
    const heroTitle = heroCopy.querySelector('h1, h2');
    const heroLine = heroCopy.querySelector('.isl-line');
    const hud = q('[data-isl-hud]');
    const fadeOut = qa('[data-isl-fadeout]');
    const fog = q('[data-isl-fog]');
    const rocks = qa('[data-isl-rock]').map((el) => ({ el, x: +el.dataset.x, y: +el.dataset.y, d: +el.dataset.depth }));
    const vWords = qa('[data-isl-vword]');
    const vFade = qa('[data-isl-vfade]');
    const acct = q('[data-isl-acct]');
    acct.innerHTML = acct.textContent.trim().split(/\s+/).map((w) => `<span class="isl-w">${w.replace(/[&<>]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' })[c])}</span>`).join(' ');
    const aWords = qa('[data-isl-acct] .isl-w');
    const hotspots = qa('[data-isl-hs]');
    const cred = q('[data-isl-cred]');
    const card = q('[data-isl-card]');
    const cnum = q('[data-isl-cnum]');
    const icons = qa('[data-isl-icon]');
    const rows = qa('[data-isl-row]');
    const prog = q('[data-isl-prog]');
    const cardTarget = cnum ? Number(cnum.dataset.to) : 0;
    const numberFormat = new Intl.NumberFormat(document.documentElement.lang || undefined);

    const cache = frameCache(q('[data-isl-video]'), q('[data-isl-canvas]'));
    const { state } = cache;

    /* Place the headline so the island's fern tips overlap the lower half of the letters. */
    const layoutHero = () => {
        const W = story.clientWidth;
        const H = window.innerHeight;
        if (W < 821) {
            heroCopy.style.top = '';
            heroTitle.style.fontSize = '';
            heroLine.style.whiteSpace = '';
            return;
        }
        heroLine.style.whiteSpace = 'nowrap';
        const bw = W * 1.06;
        const bh = H * 1.12;
        const k = Math.max(bw / 1600, bh / 900);
        const fernTop = -0.09 * H + (bh - 900 * k) / 2 + 245 * k + 0.06 * H;
        let fs = Math.min(104, 72 * k);
        heroTitle.style.fontSize = `${fs}px`;
        const lw = heroLine.firstElementChild.offsetWidth;
        if (lw > W * 0.86) {
            fs *= (W * 0.86) / lw;
            heroTitle.style.fontSize = `${fs}px`;
        }
        heroCopy.style.top = `${fernTop + 0.5 * fs - heroCopy.offsetHeight}px`;
    };

    /* Hotspots toggle their value open, one at a time. */
    hotspots.forEach((spot) => spot.addEventListener('click', () => {
        const on = !spot.classList.contains('on');
        hotspots.forEach((other) => other.classList.remove('on'));
        spot.classList.toggle('on', on);
    }));

    /* The stage slides under the sticky site header, so it needs the header's height. */
    const header = document.querySelector('[data-site-header]');
    const measureHeader = () => {
        document.documentElement.style.setProperty('--header-offset', `${header?.offsetHeight ?? 0}px`);
    };
    measureHeader();
    root.setAttribute('data-ready', '');

    let sp = 0, sx = 0, sy = 0, mx = 0, my = 0, last = performance.now(), running = false;
    window.addEventListener('pointermove', (event) => {
        if (reduced.matches) return;
        mx = event.clientX / window.innerWidth - 0.5;
        my = event.clientY / window.innerHeight - 0.5;
    }, { passive: true });

    const update = (now) => {
        const dt = Math.min(64, now - last);
        last = now;
        const rect = story.getBoundingClientRect();
        const p = clamp(-rect.top / (story.offsetHeight - window.innerHeight));
        sp = reduced.matches ? p : sp + (p - sp) * (1 - Math.pow(0.86, dt / 16.7));
        if (Math.abs(p - sp) < 0.0002) sp = p;
        sx += (mx - sx) * 0.06;
        sy += (my - sy) * 0.06;

        // video scrub
        const vp = ease(seg(sp, 0.05, 0.7));
        if (state.NF) {
            state.want = Math.round(vp * (state.NF - 1));
            cache.drawWant();
        }

        // camera
        const camT = `translate3d(${-sx * 18}px,${-sy * 12 + (1 - vp) * window.innerHeight * 0.06}px,0) scale(${1 + 0.02 * (1 - vp)})`;
        cams.forEach((c) => { c.style.transform = camT; });

        // hand-off: both high-res layers dissolve into the identical cached frame 0
        const ho = state.shown < 0 ? 0 : (state.shown === 0 ? seg(sp, 0.06, 0.105) : 1);
        plate.style.opacity = 1 - ho;
        setVisible(plate, ho < 1);
        camF.style.opacity = 1 - ho;
        setVisible(camF, ho < 1 && cutout.complete && cutout.naturalWidth > 0);

        // hero copy
        const h = seg(sp, 0.02, 0.1);
        heroCopy.style.transform = `translate3d(${-sx * 10}px,${-h * 80 - sy * 6}px,0) scale(${1 + h * 0.12})`;
        heroCopy.style.filter = `blur(${h * 14}px)`;
        heroCopy.style.opacity = 1 - h;
        hud.style.transform = `translate3d(${sx * 26}px,${sy * 18 - h * 40}px,0)`;
        hud.style.opacity = 1 - seg(sp, 0.02, 0.08);
        const captionOpacity = 1 - seg(sp, 0.01, 0.05);
        fadeOut.forEach((el) => { el.style.opacity = captionOpacity; });
        setVisible(L0, h < 1);
        setVisible(L1, h < 1);

        // rocks fly past the camera
        const fp = seg(sp, 0.02, 0.2);
        const fe = fp * fp;
        rocks.forEach((r) => {
            r.el.style.transform = `translate3d(${r.x * fp * 70 + sx * -24 * r.d}vw,${r.y * fp * 60 + sy * -16 * r.d}vh,0) scale(${1 + fe * 1.6 * r.d})`;
            r.el.style.filter = `blur(${fe * 5 * r.d}px)`;
            setVisible(r.el, fp < 1);
        });

        // fog bank rolls in from the left
        const fg = ease(seg(sp, 0.38, 0.47));
        setVisible(fog, fg > 0);
        fog.style.opacity = fg;
        fog.style.transform = `translate3d(${(fg - 1) * 18 + sx * -10}vw,${sy * -6}px,0)`;

        // vision
        const vIn = seg(sp, 0.16, 0.27);
        const vOut = seg(sp, 0.34, 0.42);
        setVisible(L2, vIn > 0 && vOut < 1);
        if (vIn > 0 && vOut < 1) {
            vWords.forEach((w, i) => {
                const k = clamp(vIn * 1.6 - i * 0.08);
                w.style.transform = `translate3d(0,${(1 - k) * 30 - vOut * 60}px,0)`;
                w.style.filter = `blur(${(1 - k) * 12 + vOut * 14}px)`;
                w.style.opacity = k * (1 - vOut);
            });
            const fade = clamp(vIn * 2 - 0.6) * (1 - vOut);
            vFade.forEach((el) => {
                el.style.opacity = fade;
                el.style.transform = `translateY(${-vOut * 50}px)`;
            });
        }

        // accounting paragraph + hotspots
        const aIn = seg(sp, 0.45, 0.55);
        const aOut = seg(sp, 0.63, 0.68);
        setVisible(L3, aIn > 0 && aOut < 1);
        if (aIn > 0 && aOut < 1) {
            aWords.forEach((w, i) => {
                const k = clamp(aIn * 2.2 - (i / aWords.length) * 1.2);
                w.style.opacity = (0.15 + 0.85 * k) * (1 - aOut);
                w.style.filter = `blur(${(1 - k) * 6 + aOut * 10}px)`;
            });
            hotspots.forEach((el, i) => {
                const k = seg(sp, 0.49 + i * 0.02, 0.53 + i * 0.02);
                el.style.transform = `translate3d(${sx * -30}px,${sy * -20}px,0) scale(${k * (1 - aOut)})`;
            });
        }

        // station
        const sIn = seg(sp, 0.72, 0.82);
        const cIn = ease(seg(sp, 0.74, 0.86));
        setVisible(L4, sIn > 0 || cIn > 0);
        if (sIn > 0 || cIn > 0) {
            cred.style.opacity = sIn;
            cred.style.transform = `translateY(${(1 - sIn) * 30}px)`;
            cred.style.filter = `blur(${(1 - sIn) * 8}px)`;
            if (card) {
                card.style.opacity = cIn;
                card.style.transform = `translate3d(${sx * -14}px,${(1 - cIn) * 60 + sy * -10}px,0) rotateY(${sx * 8}deg) rotateX(${-sy * 8}deg)`;
                cnum.textContent = numberFormat.format(Math.round(cardTarget * seg(sp, 0.76, 0.9)));
                // service icons pop in one by one as the count climbs
                icons.forEach((el, i) => {
                    const k = ease(seg(sp, 0.77 + i * 0.016, 0.8 + i * 0.016));
                    el.style.opacity = k;
                    el.style.transform = `translateY(${(1 - k) * 8}px) scale(${0.6 + 0.4 * k})`;
                });
            }
            rows.forEach((el, i) => {
                const k = ease(seg(sp, 0.76 + i * 0.025, 0.82 + i * 0.025));
                el.style.opacity = k;
                el.style.transform = `translateX(${(1 - k) * 40}px)`;
            });
        }

        prog.style.transform = `scaleX(${sp})`;
    };

    const frame = (now) => {
        if (!running) return;
        update(now);
        requestAnimationFrame(frame);
    };

    const start = () => {
        if (running) return;
        running = true;
        last = performance.now();
        requestAnimationFrame(frame);
    };

    /* Start loading the clip a screen early; run the loop only while the story is on (or near) screen. */
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                cache.load();
                start();
            } else {
                running = false;
            }
        });
    }, { rootMargin: '100% 0px' });
    io.observe(story);

    /* The headline rises in once the stage is actually on screen. */
    const reveal = new IntersectionObserver((entries) => {
        if (entries.some((entry) => entry.isIntersecting)) {
            root.setAttribute('data-in-view', '');
            reveal.disconnect();
        }
    }, { rootMargin: '0px 0px -40% 0px' });
    reveal.observe(story);

    const relayout = () => {
        measureHeader();
        cache.resize();
        layoutHero();
    };
    window.addEventListener('resize', relayout);
    relayout();
    document.fonts?.ready.then(layoutHero);
    update(performance.now()); // paint the initial state once, before the loop starts
}
