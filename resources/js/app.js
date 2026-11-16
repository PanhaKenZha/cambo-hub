/*
 * Progressive enhancements for server-rendered pages. Everything works
 * without JavaScript; these add theme switching, accessible menus, and
 * loading feedback on form submission.
 */

import { initNatureScenes } from './nature-scenes';
import { initIslandStory } from './island-story';

const THEME_KEY = 'theme';
const darkQuery = window.matchMedia('(prefers-color-scheme: dark)');

function storedTheme() {
    try {
        return localStorage.getItem(THEME_KEY);
    } catch {
        return null;
    }
}

function applyTheme(isDark) {
    document.documentElement.classList.toggle('dark', isDark);
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', String(isDark));
    });
}

function initThemeToggle() {
    applyTheme(document.documentElement.classList.contains('dark'));

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const isDark = !document.documentElement.classList.contains('dark');
            applyTheme(isDark);
            try {
                localStorage.setItem(THEME_KEY, isDark ? 'dark' : 'light');
            } catch {
                /* Storage unavailable: the choice lasts for this page only. */
            }
        });
    });

    darkQuery.addEventListener('change', (event) => {
        if (!storedTheme()) {
            applyTheme(event.matches);
        }
    });
}

/*
 * Disclosure buttons: <button data-disclosure aria-controls="id" aria-expanded="false">.
 * Escape and clicking outside close the panel and return focus to the button.
 */
function initDisclosures() {
    const buttons = [...document.querySelectorAll('[data-disclosure]')];

    const close = (button, restoreFocus = false) => {
        const panel = document.getElementById(button.getAttribute('aria-controls'));
        button.setAttribute('aria-expanded', 'false');
        if (panel) {
            panel.hidden = true;
        }
        if (restoreFocus) {
            button.focus();
        }
    };

    buttons.forEach((button) => {
        const panel = document.getElementById(button.getAttribute('aria-controls'));
        if (!panel) {
            return;
        }

        button.addEventListener('click', () => {
            const willOpen = button.getAttribute('aria-expanded') !== 'true';
            /* Close other open menus, except the one this button sits inside (e.g. a submenu in the mobile menu). */
            buttons
                .filter((other) => other !== button && !document.getElementById(other.getAttribute('aria-controls'))?.contains(button))
                .forEach((other) => close(other));
            button.setAttribute('aria-expanded', String(willOpen));
            panel.hidden = !willOpen;
        });

        panel.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                close(button, true);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }
        buttons.filter((button) => button.getAttribute('aria-expanded') === 'true').forEach((button) => close(button, true));
    });

    document.addEventListener('click', (event) => {
        buttons.forEach((button) => {
            const panel = document.getElementById(button.getAttribute('aria-controls'));
            if (button.getAttribute('aria-expanded') === 'true' && !button.contains(event.target) && !panel?.contains(event.target) && button.dataset.disclosure !== 'persistent') {
                close(button);
            }
        });
    });
}

/*
 * Forms marked data-loading show a spinner on the submit button and block
 * double submission. Native validation still runs first.
 */
function initLoadingForms() {
    document.querySelectorAll('form[data-loading]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }

            form.dataset.submitting = 'true';
            form.setAttribute('aria-busy', 'true');

            /* Optional skeleton: data-loading-target="id" is revealed, data-loading-hide="id" hidden. */
            const target = form.dataset.loadingTarget && document.getElementById(form.dataset.loadingTarget);
            const hide = form.dataset.loadingHide && document.getElementById(form.dataset.loadingHide);
            if (target) {
                target.hidden = false;
            }
            if (hide) {
                hide.hidden = true;
            }

            const submitter = event.submitter ?? form.querySelector('[type="submit"]');
            if (submitter) {
                submitter.setAttribute('aria-disabled', 'true');
                submitter.classList.add('is-loading');
                submitter.querySelector('[data-loading-indicator]')?.removeAttribute('hidden');
                const label = submitter.querySelector('[data-loading-label]');
                if (label && submitter.dataset.loadingText) {
                    label.textContent = submitter.dataset.loadingText;
                }
            }
        });
    });

    /* Restore buttons when the page is shown again from the back/forward cache. */
    window.addEventListener('pageshow', (event) => {
        if (!event.persisted) {
            return;
        }
        document.querySelectorAll('form[data-submitting]').forEach((form) => {
            delete form.dataset.submitting;
            form.removeAttribute('aria-busy');
            if (form.dataset.loadingTarget) {
                document.getElementById(form.dataset.loadingTarget)?.setAttribute('hidden', '');
            }
            if (form.dataset.loadingHide) {
                document.getElementById(form.dataset.loadingHide)?.removeAttribute('hidden');
            }
            form.querySelectorAll('.is-loading').forEach((button) => {
                button.classList.remove('is-loading');
                button.removeAttribute('aria-disabled');
                button.querySelector('[data-loading-indicator]')?.setAttribute('hidden', '');
            });
        });
    });
}

/* Remove the shimmer placeholder once lazy media has loaded. */
function initMediaPlaceholders() {
    document.querySelectorAll('img[data-placeholder]').forEach((image) => {
        const done = () => image.closest('.skeleton')?.classList.remove('skeleton');
        if (image.complete) {
            done();
        } else {
            image.addEventListener('load', done, { once: true });
            image.addEventListener('error', done, { once: true });
        }
    });
}

/* Dismissible announcements stay hidden once closed (per browser). */
function initAnnouncements() {
    const key = 'dismissed-announcements';
    let dismissed = [];
    try {
        dismissed = JSON.parse(localStorage.getItem(key) ?? '[]');
    } catch {
        dismissed = [];
    }

    document.querySelectorAll('[data-announcement]').forEach((banner) => {
        if (dismissed.includes(banner.dataset.announcement)) {
            banner.remove();
            return;
        }
        banner.querySelector('[data-dismiss-announcement]')?.addEventListener('click', () => {
            dismissed.push(banner.dataset.announcement);
            try {
                localStorage.setItem(key, JSON.stringify(dismissed.slice(-50)));
            } catch {
                /* Storage unavailable: hide for this page view only. */
            }
            banner.remove();
        });
    });
}

/* Focus the first invalid field after a failed server-side validation. */
function focusFirstError() {
    const invalid = document.querySelector('[aria-invalid="true"]');
    if (invalid && !document.querySelector('[autofocus]:focus')) {
        invalid.focus({ preventScroll: false });
    }
}

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

/*
 * Elements marked data-reveal fade up when they scroll into view. Inside
 * data-reveal-stagger, list items follow one after another.
 */
function initScrollReveal() {
    const targets = document.querySelectorAll('[data-reveal], [data-reveal-stagger]');
    if (!targets.length) {
        return;
    }

    targets.forEach((target) => {
        if (target.hasAttribute('data-reveal-stagger')) {
            target.querySelectorAll('li').forEach((item, index) => item.style.setProperty('--stagger-index', String(Math.min(index, 10))));
        }
    });

    if (reducedMotion.matches || !('IntersectionObserver' in window)) {
        targets.forEach((target) => target.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

    targets.forEach((target) => observer.observe(target));
}

/* Numbers marked data-count-to count up from zero the first time they are seen. */
function initCountUp() {
    const counters = document.querySelectorAll('[data-count-to]');
    if (!counters.length || reducedMotion.matches || !('IntersectionObserver' in window)) {
        return;
    }

    const format = new Intl.NumberFormat(document.documentElement.lang || undefined);
    const run = (element) => {
        const target = Number(element.dataset.countTo);
        const start = performance.now();
        const duration = 1400;
        const tick = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            element.textContent = format.format(Math.round(target * eased));
            if (progress < 1) {
                requestAnimationFrame(tick);
            }
        };
        requestAnimationFrame(tick);
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                run(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach((counter) => observer.observe(counter));
}

/* Floating header state and the reading-progress bar. */
function initScrollHeader() {
    const header = document.querySelector('[data-site-header]');
    const progress = document.querySelector('[data-scroll-progress]');
    if (!header && !progress) {
        return;
    }

    let queued = false;
    const update = () => {
        queued = false;
        const scrolled = window.scrollY;
        header?.toggleAttribute('data-scrolled', scrolled > 12);
        if (progress) {
            const max = document.documentElement.scrollHeight - window.innerHeight;
            progress.style.setProperty('--scroll-progress', max > 0 ? String(Math.min(scrolled / max, 1)) : '0');
        }
    };

    window.addEventListener('scroll', () => {
        if (!queued) {
            queued = true;
            requestAnimationFrame(update);
        }
    }, { passive: true });
    update();
}

/* Cards with the spotlight utility get a glow that follows the pointer. */
function initSpotlight() {
    if (reducedMotion.matches || !window.matchMedia('(hover: hover)').matches) {
        return;
    }

    document.addEventListener('pointermove', (event) => {
        const card = event.target instanceof Element ? event.target.closest('.spotlight') : null;
        if (!card) {
            return;
        }
        const rect = card.getBoundingClientRect();
        card.style.setProperty('--spot-x', `${event.clientX - rect.left}px`);
        card.style.setProperty('--spot-y', `${event.clientY - rect.top}px`);
    }, { passive: true });
}

initThemeToggle();
initDisclosures();
initScrollReveal();
initCountUp();
initScrollHeader();
initSpotlight();
initNatureScenes();
initIslandStory();
initLoadingForms();
initMediaPlaceholders();
initAnnouncements();
focusFirstError();
