/* Every supplied frame is preloaded in order and drawn to a canvas in step with scrolling. */
function createFrameCanvas(image, frames) {
    const canvas = document.createElement('canvas');
    canvas.className = 'hero-frame-canvas';
    canvas.setAttribute('aria-hidden', 'true');
    image.after(canvas);
    const context = canvas.getContext('2d', { alpha: false });
    const loaded = new Array(frames.length);
    // Frame numbers from the file names (frame_00042.webp → 42); falls back to even spacing.
    const parsed = frames.map((src) => Number(/(\d+)\.\w+(?:\?.*)?$/.exec(src)?.[1]));
    const numbers = parsed.every((value, index) => Number.isFinite(value) && (index === 0 || value > parsed[index - 1]))
        ? parsed
        : frames.map((_, index) => index);
    let desiredIndex = 0;
    let shownIndex = -1;
    let targetIndex = 0;
    let drawnIndex = -1;
    let drawnFrame = null;
    let preloading = false;
    let ambientTimer = null;

    const resize = () => {
        const ratio = Math.min(window.devicePixelRatio || 1, 2);
        const width = Math.max(1, Math.round(canvas.clientWidth * ratio));
        const height = Math.max(1, Math.round(canvas.clientHeight * ratio));
        if (canvas.width !== width || canvas.height !== height) {
            canvas.width = width;
            canvas.height = height;
            drawnIndex = -1;
        }
    };
    const nearestLoaded = (index) => {
        // Prefer the latest loaded frame at or before this point, so playback only moves forward.
        for (let candidate = index; candidate >= 0; candidate--) {
            if (loaded[candidate]) {
                return candidate;
            }
        }
        for (let candidate = index + 1; candidate < frames.length; candidate++) {
            if (loaded[candidate]) {
                return candidate;
            }
        }
        return -1;
    };
    // Cover-fit a frame, matching object-fit: cover on the poster image.
    const paint = (frame, alpha) => {
        const scale = Math.max(canvas.width / frame.naturalWidth, canvas.height / frame.naturalHeight);
        const width = frame.naturalWidth * scale;
        const height = frame.naturalHeight * scale;
        context.globalAlpha = alpha;
        context.drawImage(frame, (canvas.width - width) / 2, (canvas.height - height) / 2, width, height);
        context.globalAlpha = 1;
    };
    const draw = () => {
        const index = nearestLoaded(desiredIndex);
        if (index < 0 || !context) {
            return false;
        }
        resize();
        const frame = loaded[index];
        if (index !== drawnIndex || frame !== drawnFrame) {
            paint(frame, 1);
            drawnIndex = index;
            drawnFrame = frame;
        }
        return true;
    };
    const preload = (onProgress) => {
        if (preloading) {
            return;
        }
        preloading = true;
        // Load in playback order, so the animation never has to jump backwards to a loaded frame.
        const order = frames.map((_, index) => index);
        let next = 0;
        const loadNext = () => {
            if (next >= order.length) {
                return;
            }
            const index = order[next++];
            const frame = new Image();
            frame.decoding = 'async';
            frame.onload = () => {
                const ready = frame.decode ? frame.decode().catch(() => {}) : Promise.resolve();
                void ready.then(() => {
                    loaded[index] = frame;
                    onProgress();
                    loadNext();
                });
            };
            frame.onerror = loadNext;
            frame.src = frames[index];
        };
        // A few parallel lanes keep the network busy without flooding it.
        for (let lane = 0; lane < 4; lane++) {
            loadNext();
        }
    };

    return {
        canvas,
        preload,
        show(progress) {
            // Time follows the original frame numbers, so a missing range holds the frame before it.
            const target = numbers[0] + progress * (numbers[numbers.length - 1] - numbers[0]);
            let index = 0;
            while (index + 1 < numbers.length && numbers[index + 1] <= target + 0.5) {
                index++;
            }
            targetIndex = index;
            // Step through the frames in order, so a quick scroll still shows every one of them.
            // Large jumps (a link to another part of the page) catch up within about 12 screen refreshes.
            const distance = targetIndex - shownIndex;
            if (shownIndex < 0) {
                shownIndex = targetIndex;
            } else if (distance !== 0) {
                shownIndex += Math.sign(distance) * Math.max(1, Math.ceil(Math.abs(distance) / 12));
            }
            if (ambientTimer) {
                return drawnIndex >= 0;
            }
            desiredIndex = shownIndex;
            return draw();
        },
        // Behind the plant, the closing field frames drift slowly back and forth, cross-fading
        // from one frame into the next so the field breathes like wind instead of flickering.
        setAmbient(on) {
            if (on && !ambientTimer) {
                const lastNumber = numbers[numbers.length - 1];
                const startedAt = performance.now();
                let lastPaint = 0;
                const tick = (now) => {
                    ambientTimer = requestAnimationFrame(tick);
                    if (now - lastPaint < 33 || document.hidden || !context) {
                        return;
                    }
                    lastPaint = now;
                    const pool = numbers
                        .map((number, index) => (number >= lastNumber - 20 && loaded[index] ? index : -1))
                        .filter((index) => index >= 0);
                    if (pool.length < 2) {
                        return;
                    }
                    // A 10-second ease in and out, starting on the last frame so the handover is seamless.
                    const wave = (1 - Math.cos(((now - startedAt) / 10000) * Math.PI * 2)) / 2;
                    const position = (pool.length - 1) * (1 - wave);
                    const lower = Math.floor(position);
                    const upper = Math.min(pool.length - 1, lower + 1);
                    resize();
                    paint(loaded[pool[lower]], 1);
                    if (upper !== lower) {
                        paint(loaded[pool[upper]], position - lower);
                    }
                    drawnIndex = -1;
                };
                ambientTimer = requestAnimationFrame(tick);
            } else if (!on && ambientTimer) {
                cancelAnimationFrame(ambientTimer);
                ambientTimer = null;
                desiredIndex = shownIndex;
                draw();
            }
        },
        redraw: () => draw(),
        get pending() {
            return shownIndex !== targetIndex;
        },
        invalidate() {
            drawnIndex = -1;
        },
    };
}

/* Native scroll controls the supplied field frames and layered plant artwork. */
export function initNatureScenes() {
    const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
    const sequences = [...document.querySelectorAll('[data-frame-sequence]')];
    const journeys = [...document.querySelectorAll('[data-growth-journey]')];
    if (!sequences.length && !journeys.length) {
        return;
    }

    const clamp = (value, minimum = 0, maximum = 1) => Math.min(maximum, Math.max(minimum, value));
    const sceneProgress = new WeakMap();
    let lastFrameTime = 0;
    const sequenceStates = sequences.map((element) => {
        const image = element.querySelector('[data-frame-image]');
        let frames;
        try {
            frames = JSON.parse(element.dataset.frames);
        } catch {
            return null;
        }
        if (!image || !Array.isArray(frames) || frames.length < 2) {
            return null;
        }
        element.style.setProperty('--sequence-count', String(frames.length));
        const renderer = createFrameCanvas(image, frames);
        const plant = element.querySelector('.scene-growth');
        const crown = element.querySelector('.scene-growth .growth-crown');
        const chapters = [...element.querySelectorAll('[data-scene-chapter]')];
        const counter = element.querySelector('[data-growth-index]');
        let started = false;
        const start = () => {
            if (started || motionPreference.matches || navigator.connection?.saveData) {
                return;
            }
            started = true;
            element.setAttribute('data-motion-ready', '');
            renderer.preload(() => {
                if (renderer.redraw()) {
                    element.setAttribute('data-canvas-ready', '');
                }
            });
        };
        return {
            element,
            start,
            invalidate: () => renderer.invalidate(),
            pause: () => renderer.setAmbient(false),
            get pending() {
                return started && renderer.pending;
            },
            update(value) {
                if (!started) {
                    return;
                }
                // One pinned stage: the first part of the scroll plays every field frame,
                // the rest grows the plant out of the final frame and reveals its roots.
                const hasPlant = Boolean(plant);
                const fieldShare = hasPlant ? 0.5 : 1;
                const fieldProgress = clamp(value / fieldShare);
                element.style.setProperty('--sequence-progress', String(fieldProgress));
                element.style.setProperty('--hero-fade', String(1 - clamp((value - 0.02) / (fieldShare * 0.22))));
                element.toggleAttribute('data-hero-hidden', value > 0.02 + fieldShare * 0.2);
                if (renderer.show(fieldProgress)) {
                    element.setAttribute('data-canvas-ready', '');
                }
                if (!hasPlant) {
                    return;
                }
                const growth = clamp((value - fieldShare) / (1 - fieldShare));
                const plantPhase = value > fieldShare - 0.01;
                element.toggleAttribute('data-plant-phase', plantPhase);
                // The field holds still on its last frame behind the plant (no looping camera movement).
                renderer.setAmbient(false);
                const viewportHeight = window.innerHeight;
                const crownHeight = crown?.offsetHeight || viewportHeight;
                const rise = clamp(growth / 0.25);
                const reveal = clamp((growth - 0.25) / 0.75);
                const offset = rise < 1
                    ? viewportHeight * (1.05 - rise * 0.85)
                    : viewportHeight * 0.2 + reveal * (viewportHeight * 0.38 - crownHeight - viewportHeight * 0.2);
                element.style.setProperty('--scene-dark', String(clamp((value - fieldShare + 0.04) / 0.1)));
                element.style.setProperty('--plant-in', String(rise));
                element.style.setProperty('--plant-offset', `${offset}px`);
                element.style.setProperty('--plant-scale', String(0.82 + rise * 0.18));
                element.style.setProperty('--roots-visible', String(clamp((growth - 0.45) * 3)));
                // Each chapter fades in for its part of the growth and the last one stays.
                const windows = [[0.18, 0.44], [0.46, 0.72], [0.74, 1.01]];
                let active = 0;
                chapters.forEach((chapter, index) => {
                    const [from, to] = windows[index] ?? windows[windows.length - 1];
                    const fadeIn = clamp((growth - from) / 0.06);
                    const fadeOut = index === chapters.length - 1 ? 1 : clamp((to - growth) / 0.06);
                    const opacity = Math.min(fadeIn, fadeOut);
                    chapter.style.setProperty('--chapter-opacity', String(opacity));
                    chapter.toggleAttribute('data-active', opacity > 0.5);
                    if (growth >= from) {
                        active = index + 1;
                    }
                });
                if (counter) {
                    counter.textContent = `0${Math.max(1, active)} / 0${chapters.length}`;
                }
            },
        };
    }).filter(Boolean);

    let scheduled = false;
    const update = (time) => {
        scheduled = false;
        if (motionPreference.matches) {
            return;
        }
        const elapsed = Math.min((time - lastFrameTime) / 1000 || 1 / 60, 0.064);
        lastFrameTime = time;
        let settling = false;
        const scrub = (element, target) => {
            const previous = sceneProgress.get(element) ?? target;
            const current = previous + (target - previous) * (1 - Math.exp(-elapsed / 0.14));
            if (Math.abs(current - target) > 0.0005) {
                settling = true;
            }
            sceneProgress.set(element, current);
            return current;
        };
        const viewportHeight = window.innerHeight;
        sequenceStates.forEach((state) => {
            const bounds = state.element.getBoundingClientRect();
            if (bounds.bottom < 0 || bounds.top > viewportHeight) {
                state.pause();
                return;
            }
            state.start();
            state.update(scrub(state.element, clamp(-bounds.top / Math.max(bounds.height - viewportHeight, 1))));
            if (state.pending) {
                settling = true;
            }
        });
        journeys.forEach((element) => {
            const bounds = element.getBoundingClientRect();
            if (bounds.bottom < 0 || bounds.top > viewportHeight) {
                return;
            }
            const progress = scrub(element, clamp(-bounds.top / Math.max(bounds.height - viewportHeight, 1)));
            const focus = clamp(progress * 2.5);
            element.style.setProperty('--field-opacity', String(1 - focus));
            element.style.setProperty('--plant-scale', String(0.78 + focus * 0.22));
            element.style.setProperty('--field-scale', String(1.04 + focus * 0.22));
            const crownHeight = element.querySelector('.growth-crown')?.offsetHeight ?? viewportHeight;
            const plantOffset = viewportHeight * 0.28 + progress * (viewportHeight * 0.04 - crownHeight);
            element.style.setProperty('--plant-offset', `${plantOffset}px`);
            element.style.setProperty('--roots-visible', String(clamp((progress - 0.48) * 3)));
            element.style.setProperty('--growth-progress', String(progress));
            const index = element.querySelector('[data-growth-index]');
            if (index) {
                const chapters = [...element.querySelectorAll('.growth-chapter')];
                const reached = chapters.filter((chapter) => chapter.getBoundingClientRect().top < viewportHeight * 0.6).length;
                index.textContent = `0${Math.max(1, reached)} / 0${chapters.length}`;
            }
        });
        if (settling) {
            schedule();
        }
    };
    const schedule = () => {
        if (!scheduled) {
            scheduled = true;
            requestAnimationFrame(update);
        }
    };
    const configure = () => {
        journeys.forEach((element) => element.toggleAttribute('data-motion-ready', !motionPreference.matches));
        if (motionPreference.matches) {
            [...sequences, ...journeys].forEach((element) => {
                sceneProgress.delete(element);
                element.style.removeProperty('--plant-tilt');
                element.style.removeProperty('--plant-pan');
            });
        }
        sequenceStates.forEach((state) => {
            const { element } = state;
            const enabled = !motionPreference.matches && !navigator.connection?.saveData;
            if (!enabled) {
                state.pause();
            }
            element.toggleAttribute('data-motion-ready', enabled);
            if (!enabled) {
                element.removeAttribute('data-canvas-ready');
            }
        });
        schedule();
    };

    [...sequences, ...journeys].forEach((element) => {
        element.addEventListener('pointermove', (event) => {
            if (motionPreference.matches || event.pointerType !== 'mouse') {
                return;
            }
            const bounds = element.getBoundingClientRect();
            const horizontal = clamp((event.clientX - bounds.left) / bounds.width);
            element.style.setProperty('--growth-light-x', `${horizontal * 100}%`);
            element.style.setProperty('--plant-tilt', `${(horizontal - 0.5) * 3}deg`);
            element.style.setProperty('--plant-pan', `${(horizontal - 0.5) * 16}px`);
        }, { passive: true });
        element.addEventListener('pointerleave', () => {
            element.style.setProperty('--plant-tilt', '0deg');
            element.style.setProperty('--plant-pan', '0px');
            element.style.setProperty('--growth-light-x', '60%');
        });
    });
    const motionTargets = [...sequences, ...journeys, ...document.querySelectorAll('.growth-gallery')];
    if ('IntersectionObserver' in window) {
        const visibilityObserver = new IntersectionObserver((entries) => {
            entries.forEach(({ target, isIntersecting }) => target.toggleAttribute('data-scene-visible', isIntersecting));
        }, { rootMargin: '100px' });
        motionTargets.forEach((element) => visibilityObserver.observe(element));
    } else {
        motionTargets.forEach((element) => element.setAttribute('data-scene-visible', ''));
    }
    // The full-bleed opening slides under the sticky header, so it needs the header's height.
    const header = document.querySelector('[data-site-header]');
    const measureHeader = () => {
        document.documentElement.style.setProperty('--header-offset', `${header?.offsetHeight ?? 0}px`);
    };
    measureHeader();
    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', () => {
        measureHeader();
        sequenceStates.forEach((state) => state.invalidate());
        schedule();
    }, { passive: true });
    window.addEventListener('pageshow', configure);
    motionPreference.addEventListener('change', configure);
    configure();
}
