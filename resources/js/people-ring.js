/*
 * "Built around everyday life" people ring (resources/views/components/home-people-ring.blade.php).
 *
 * Nine people stand on a ring driven by a critically damped spring. Drag, horizontal wheel, arrow keys
 * or autoplay turn it; Grid lays everyone out; opening a person zooms them in with numbered dots for
 * the services in their day, and a receipt-style card lists those services with links.
 */

const pad = (n) => String(n).padStart(2, '0');
const clamp = (n, a, b) => Math.max(a, Math.min(b, n));
const escape = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

export function initPeopleRing() {
    const root = document.querySelector('[data-people-ring]');
    if (!root) return;

    const data = JSON.parse(root.querySelector('[data-pr-data]').textContent);
    const looks = data.looks;
    const t = data.t;
    const N = looks.length;
    const mod = (n, m = N) => ((n % m) + m) % m;
    const shortest = (a, b) => mod(a - b + N / 2) - N / 2;

    const $ = (id) => root.querySelector(`#pr-${id}`);
    const deck = $('deck');
    const stage = $('stage');
    const crowd = $('crowd');
    const labels = $('labels');
    const slip = $('slip');
    const lines = $('lines');
    const focus = $('focus');
    const focusStage = $('focusStage');
    const follow = $('follow');

    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const STEP = (2 * Math.PI) / N;
    const STIFF = 95;
    const DAMP = 2 * Math.sqrt(STIFF);

    let active = 0, pos = reduce ? 0 : -1.15, goal = 0, vel = 0, last = 0, raf = 0, dirty = true, visible = false;
    let grid = false, focusOpen = false, drawerOpen = false, autoplay = !reduce, autoTimer = 0, slipHover = false, animTimer = 0;
    let drag = null, dragEnd = 0, wheelSum = 0, wheelLock = false;
    let W = 0, H = 0, modelH = 560, bodyW = 320, dragPx = 120, mobile = false, focusModel = null;

    const figHTML = (i, hit) => `<div class="model__fig"><span class="model__shadow" aria-hidden="true"></span><img class="model__pic" src="${looks[i].img}" alt="${escape(looks[i].name)}" draggable="false" decoding="async" loading="lazy">${hit ? `<button type="button" class="model__hit" data-i="${i}" tabindex="-1" aria-label="${escape(t.person)} ${pad(i + 1)}: ${escape(looks[i].name)}"></button>` : ''}</div>`;

    const bodies = looks.map((look, i) => {
        const el = document.createElement('div');
        el.className = 'model';
        el.style.setProperty('--foot', look.foot);
        el.innerHTML = figHTML(i, true);
        crowd.append(el);

        const label = document.createElement('span');
        label.className = 'deck__label cap';
        label.style.setProperty('--i', i);
        label.textContent = `${pad(i + 1)}  ${look.name}`;
        labels.append(label);

        const li = document.createElement('li');
        li.style.setProperty('--i', i);
        li.innerHTML = `<button type="button" class="drawer__item" data-i="${i}" aria-current="false"><span class="cap">${pad(i + 1)}</span><span class="drawer__name">${escape(look.name)}</span><i class="drawer__meta cap">${look.items.length} ${escape(t.services)}</i></button>`;
        $('drawerList').append(li);
        return el;
    });
    const drawerItems = [...root.querySelectorAll('.drawer__item')];
    const gridLabels = [...labels.children];

    const place = (b, x, y, s, op, blur, z) => {
        if (x + (bodyW * s) / 2 < -4 || x - (bodyW * s) / 2 > W + 4) op = 0;
        b.style.transform = `translate3d(${x.toFixed(1)}px,${(y - H).toFixed(1)}px,0) translateX(-50%) scale(${s.toFixed(4)})`;
        const o = op.toFixed(3);
        if (b.style.opacity !== o) b.style.opacity = o;
        b.style.visibility = op < 0.01 ? 'hidden' : 'visible';
        b.style.pointerEvents = op < 0.5 ? 'none' : 'auto';
        b.style.filter = blur > 0.05 ? `blur(${blur.toFixed(1)}px)` : 'none';
        b.style.zIndex = z;
    };
    const wrapAngle = (a) => {
        a = (a + Math.PI) % (2 * Math.PI);
        if (a < 0) a += 2 * Math.PI;
        return a - Math.PI;
    };

    const TOP = 72;
    const bottomRoom = () => (mobile ? 168 : 120);
    const layoutRing = () => {
        const span = H - TOP - bottomRoom();
        const cx = W * 0.5;
        const cy = TOP + span * 0.5 + 6;
        const ry = span * (mobile ? 0.34 : 0.37);
        const rx = Math.min(W * (mobile ? 0.3 : 0.28), ry * (mobile ? 1.15 : 1.7));
        const frontH = H * (mobile ? 0.19 : 0.255);
        const front = frontH / modelH;
        stage.style.setProperty('--loop-y', `${cy - frontH * 0.5 - 6}px`);
        stage.style.setProperty('--ring-y', `${cy}px`);
        $('loopRing').style.width = `${2 * rx}px`;
        $('loopRing').style.height = `${2 * ry}px`;
        bodies.forEach((b, i) => {
            const a = wrapAngle((i - pos) * STEP);
            const depth = (1 + Math.cos(a)) / 2;
            const near = clamp(1 - Math.abs(a) / STEP, 0, 1);
            const s = front * (0.5 + 0.5 * depth) * (1 + 0.14 * near);
            place(b, cx + rx * Math.sin(a), cy + ry * Math.cos(a) + modelH * s * 0.08, s, 1, Math.max(0, (1 - depth) * 2.2 - 0.3), Math.round(depth * 1000) + 10);
        });
    };
    const layoutGrid = () => {
        const top = mobile ? 72 : 80;
        const rowH = (H - (mobile ? 104 : 96) - top) / 3;
        const colW = W / 3;
        const s = Math.max(0.01, Math.min(rowH - 40, ((colW * 7) / 4) * 0.92) / modelH);
        bodies.forEach((b, i) => {
            const x = colW * ((i % 3) + 0.5);
            const y = top + rowH * (Math.floor(i / 3) + 1) - 32;
            place(b, x, y, s, 1, 0, 10 + Math.floor(i / 3));
            gridLabels[i].style.transform = `translate3d(${x}px,${y + 12}px,0) translateX(-50%)`;
        });
    };
    const measure = () => {
        W = stage.clientWidth;
        H = stage.clientHeight;
        mobile = window.innerWidth < 810;
        modelH = Math.max(200, Math.round((H - bottomRoom() - 18 - TOP) / 1.12));
        bodyW = (modelH * 4) / 7;
        dragPx = Math.max(mobile ? 90 : 120, W * 0.3);
        stage.style.setProperty('--model-h', `${modelH}px`);
        if (grid) layoutGrid();
        dirty = true;
        kick();
    };

    function kick() {
        if (!raf && visible) raf = requestAnimationFrame(tick);
    }
    function tick(time) {
        raf = 0;
        const dt = last ? Math.min(0.034, (time - last) / 1000) : 1 / 60;
        last = time;
        let moving = false;
        if (reduce) {
            pos = goal;
            vel = 0;
        } else {
            const h = dt / 3;
            for (let k = 0; k < 3; k++) {
                const acc = -STIFF * (pos - goal) - DAMP * vel;
                vel += acc * h;
                pos += vel * h;
            }
            if (Math.abs(pos - goal) <= 5e-4 && Math.abs(vel) <= 5e-4) {
                pos = goal;
                vel = 0;
            } else {
                moving = true;
            }
        }
        if ((moving || dirty || drag?.dragging) && !grid) layoutRing();
        deck.classList.toggle('is-moving', moving || !!drag?.dragging);
        dirty = false;
        if (moving || drag?.dragging) kick();
    }

    const scheduleAuto = () => {
        clearTimeout(autoTimer);
        if (autoplay) {
            autoTimer = setTimeout(() => {
                if (visible && !drag?.dragging && !document.hidden && !slipHover && !focusOpen && !grid && !drawerOpen) next();
                else scheduleAuto();
            }, 5000);
        }
    };
    const setAutoplay = (on) => {
        autoplay = on;
        $('auto').setAttribute('aria-pressed', String(on));
        $('auto').innerHTML = on
            ? '<svg viewBox="0 0 24 24"><path d="M6.5 4.5h3.5v15H6.5zM14 4.5h3.5v15H14z"/></svg>'
            : '<svg viewBox="0 0 24 24"><path d="M7 4.5v15l12.5-7.5z"/></svg>';
        scheduleAuto();
    };
    const stopAuto = () => { if (autoplay) setAutoplay(false); };
    const restart = (el, cls) => {
        el.classList.remove(cls);
        void el.offsetWidth;
        el.classList.add(cls);
    };

    const renderPieces = (animate) => {
        $('pieces').innerHTML = looks[active].items.map((it, p) => `<li style="--i:${p + 1}"><button type="button" class="piece" data-p="${p + 1}" aria-label="${escape(it.name)}, ${escape(it.kind)}"><span class="piece__img"><img src="${it.iconUrl}" alt="" width="22" height="22"></span><span class="piece__copy"><span class="piece__name">${escape(it.name)}</span><i class="piece__kind">${escape(it.kind)}</i></span></button></li>`).join('');
        $('pieces').classList.remove('is-in');
        if (animate) restart($('pieces'), 'is-in');
    };
    const renderSlip = () => {
        const look = looks[active];
        $('slipLook').textContent = `${t.person} ${pad(active + 1)} — ${look.name}`;
        lines.innerHTML = look.items.map((it, p) => `<li class="line" data-p="${p + 1}"><img class="line__icon" src="${it.iconUrl}" alt=""><span class="line__name">${escape(it.name)}</span><span class="line__kind cap">${escape(it.kind)}</span><a class="line__open" href="${it.url}">${escape(t.open)}<svg viewBox="0 0 12 12" aria-hidden="true"><path d="M3 9l6-6M4 3h5v5"/></svg></a></li>`).join('');
    };

    const setActive = (i, quiet = false) => {
        i = mod(i);
        if (i === active && !quiet) return;
        active = i;
        bodies.forEach((b, n) => { b.querySelector('.model__hit').tabIndex = n === active ? 0 : -1; });
        drawerItems.forEach((b, n) => b.setAttribute('aria-current', String(n === active)));
        gridLabels.forEach((b, n) => b.classList.toggle('is-on', n === active));
        $('nowIdx').textContent = pad(i + 1);
        $('nowName').textContent = looks[i].name;
        if (!quiet) {
            $('live').textContent = `${t.person} ${i + 1}: ${looks[i].name}`;
            if (!reduce) {
                restart(deck, 'is-glint');
                restart($('now'), 'is-swap');
            }
            if (focusOpen) closeFocus();
        }
        renderSlip();
        renderPieces(!quiet && !reduce);
        scheduleAuto();
    };
    const goTo = (g) => {
        goal = g;
        setActive(mod(g));
        dirty = true;
        kick();
    };
    const next = () => goTo(Math.round(goal) + 1);
    const prev = () => goTo(Math.round(goal) - 1);
    const jumpTo = (i) => goTo(Math.round(goal) + shortest(i, active));

    const fitTip = (tag) => {
        tag.classList.remove('is-flip', 'is-below');
        tag.style.removeProperty('--dx');
        const tip = tag.querySelector('.tag__tip');
        const tr = tag.getBoundingClientRect();
        const sr = focusStage.getBoundingClientRect();
        const tw = tip.getBoundingClientRect().width;
        if (tr.right + tw <= sr.right - 8) return;
        if (tr.left - tw >= sr.left + 8) {
            tag.classList.add('is-flip');
            return;
        }
        tag.classList.add('is-below');
        const half = Math.min(tw, sr.width - 16) / 2;
        const mid = tr.left + tr.width / 2;
        tag.style.setProperty('--dx', `${clamp(mid, sr.left + 8 + half, sr.right - 8 - half) - mid}px`);
    };
    const setHot = (p, scroll = false) => {
        root.querySelectorAll('.tag, .line').forEach((e) => e.classList.remove('is-hot'));
        if (!p) return;
        const tag = focusStage.querySelector(`.tag[data-p="${p}"]`);
        const line = lines.querySelector(`.line[data-p="${p}"]`);
        if (tag) {
            fitTip(tag);
            tag.classList.add('is-hot');
        }
        if (line) {
            line.classList.add('is-hot');
            if (scroll) line.scrollIntoView({ block: 'nearest', behavior: reduce ? 'auto' : 'smooth' });
        }
    };

    function openFocus() {
        if (focusOpen) return;
        stopAuto();
        setHot(0);
        if (drawerOpen) setDrawer(false);
        const i = active;
        const look = looks[i];
        const src = bodies[i].querySelector('.model__fig');
        $('focusNum').textContent = `${pad(i + 1)}.`;
        $('focusName').textContent = look.name;
        const rect = focusStage.getBoundingClientRect();
        const h = Math.max(160, Math.min(rect.height, rect.width / (4 / 7)));
        focusStage.innerHTML = '';
        const fm = document.createElement('div');
        fm.className = 'focus__model';
        fm.style.setProperty('--model-h', `${h}px`);
        fm.style.setProperty('--foot', look.foot);
        fm.innerHTML = figHTML(i, false) + `<div class="tags">${look.items.map((it, p) => `<a class="tag" href="${it.url}" data-p="${p + 1}" style="left:${look.tags[p][0]}%;top:${look.tags[p][1]}%;--pi:${p + 1}" aria-label="${pad(p + 1)}: ${escape(it.name)}, ${escape(it.kind)}"><span class="dotnum">${pad(p + 1)}</span><span class="tag__tip"><img src="${it.iconUrl}" alt=""><b>${escape(it.name)}</b><span>${escape(it.kind)}</span></span></a>`).join('')}</div>`;
        focusStage.append(fm);
        focusModel = fm;
        const from = src.getBoundingClientRect();
        const to = fm.getBoundingClientRect();
        focusOpen = true;
        deck.classList.add('is-focus');
        focus.setAttribute('aria-hidden', 'false');
        slip.setAttribute('aria-hidden', 'false');
        if (!reduce) {
            fm.style.transition = 'none';
            fm.style.transform = `translate3d(${from.left - to.left}px,${from.bottom - to.bottom}px,0) scale(${from.height / to.height})`;
            void fm.offsetWidth;
            fm.style.transition = 'transform .95s var(--ease)';
            fm.style.transform = 'none';
        }
        $('focusClose').focus({ preventScroll: true });
    }
    function closeFocus() {
        if (!focusOpen) return;
        const fm = focusModel;
        const from = bodies[active].querySelector('.model__fig').getBoundingClientRect();
        const to = fm?.getBoundingClientRect();
        if (fm && !reduce && to) fm.style.transform = `translate3d(${from.left - to.left}px,${from.bottom - to.bottom}px,0) scale(${from.height / to.height})`;
        focusOpen = false;
        deck.classList.remove('is-focus');
        focus.setAttribute('aria-hidden', 'true');
        slip.setAttribute('aria-hidden', 'true');
        setHot(0);
        setTimeout(() => {
            if (!focusOpen) {
                fm?.remove();
                if (focusModel === fm) focusModel = null;
            }
        }, reduce ? 0 : 950);
        if (focus.contains(document.activeElement) || slip.contains(document.activeElement)) stage.focus({ preventScroll: true });
    }
    function setDrawer(open) {
        if (open === drawerOpen) return;
        if (open) {
            stopAuto();
            if (focusOpen) closeFocus();
        }
        drawerOpen = open;
        deck.classList.toggle('is-drawer', open);
        $('drawer').setAttribute('aria-hidden', String(!open));
        $('drawerBtn').setAttribute('aria-expanded', String(open));
        if (open) drawerItems[active].focus({ preventScroll: true });
        else if ($('drawer').contains(document.activeElement)) $('drawerBtn').focus({ preventScroll: true });
        scheduleAuto();
    }
    const setGrid = (on) => {
        if (grid === on || focusOpen) return;
        stopAuto();
        clearTimeout(animTimer);
        bodies.forEach((b, i) => {
            const d = Math.round((Math.abs(wrapAngle((i - pos) * STEP)) / Math.PI) * 4);
            b.style.setProperty('--d', `${d * (on ? 0.32 : 0.24)}s`);
        });
        deck.classList.add('is-anim');
        grid = on;
        deck.classList.toggle('is-grid', grid);
        root.querySelectorAll('.deck__vbtn').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.v === (on ? 'grid' : 'ring'))));
        stage.setAttribute('aria-roledescription', on ? 'grid' : 'carousel');
        if (on) {
            layoutGrid();
        } else {
            bodies.forEach((b) => { b.style.opacity = ''; b.style.filter = ''; });
            dirty = true;
            layoutRing();
        }
        animTimer = setTimeout(() => deck.classList.remove('is-anim'), reduce ? 0 : 1700);
        kick();
    };

    /* Cursor follower ("Drag" / "View"). */
    let fX = 0, fY = 0, fTargetX = 0, fTargetY = 0, fVisible = false, followRaf = 0;
    const trackFollow = () => {
        followRaf = 0;
        const k = reduce ? 1 : 0.18;
        fX += (fTargetX - fX) * k;
        fY += (fTargetY - fY) * k;
        follow.style.transform = `translate3d(${fX}px,${fY}px,0)`;
        if (Math.abs(fX - fTargetX) + Math.abs(fY - fTargetY) > 0.4) followRaf = requestAnimationFrame(trackFollow);
    };
    const followMove = (e) => {
        if (!root.contains(follow) || e.pointerType !== 'mouse') return;
        const r = stage.getBoundingClientRect();
        fTargetX = e.clientX - r.left;
        fTargetY = e.clientY - r.top;
        if (!fVisible) {
            fX = fTargetX;
            fY = fTargetY;
            fVisible = true;
        }
        follow.classList.add('is-on');
        const hit = e.target.closest?.('.model__hit');
        const idle = !drag?.dragging && !focusOpen;
        follow.classList.toggle('is-view', !!hit && (grid || Number(hit.dataset.i) === active) && idle);
        follow.classList.toggle('is-link', !!hit && !grid && Number(hit.dataset.i) !== active && idle);
        if (!followRaf) followRaf = requestAnimationFrame(trackFollow);
    };

    /* Drag to turn. */
    const releaseDrag = (e) => {
        if (!drag || drag.id !== e.pointerId) return;
        if (drag.dragging) {
            const tr = drag.trail;
            const first = tr[0];
            const end = tr[tr.length - 1];
            const v = (end.x - first.x) / Math.max(1, end.t - first.t);
            const dx = e.clientX - drag.x;
            const base = Math.round(drag.startGoal);
            let target;
            if (mobile) {
                target = Math.abs(dx) > 30 || Math.abs(v) > 0.3 ? base + (dx < 0 ? 1 : -1) : base;
            } else {
                target = Math.round(goal - (v * 160) / dragPx);
                if (target === base && Math.abs(v) > 0.3) target = base + (v < 0 ? 1 : -1);
                target = clamp(target, base - 2, base + 2);
            }
            goTo(target);
            dragEnd = performance.now();
        }
        if (stage.hasPointerCapture?.(e.pointerId)) stage.releasePointerCapture(e.pointerId);
        drag = null;
        deck.classList.remove('is-dragging');
    };
    stage.addEventListener('pointermove', (e) => {
        followMove(e);
        if (!drag || e.pointerId !== drag.id) return;
        if (e.pointerType === 'mouse' && !(e.buttons & 1)) {
            releaseDrag(e);
            return;
        }
        const dx = e.clientX - drag.x;
        const dy = e.clientY - drag.y;
        if (!drag.dragging) {
            if (Math.abs(dy) > 10 && Math.abs(dy) > Math.abs(dx)) {
                drag = null;
                return;
            }
            if (Math.abs(dx) > 8 && Math.abs(dx) > 1.2 * Math.abs(dy)) {
                drag.dragging = true;
                deck.classList.add('is-dragging');
                stage.setPointerCapture(e.pointerId);
                stopAuto();
            }
        }
        if (drag?.dragging) {
            drag.trail.push({ x: e.clientX, t: performance.now() });
            if (drag.trail.length > 6) drag.trail.shift();
            goal = drag.startGoal - dx / dragPx;
            setActive(mod(Math.round(goal)));
            kick();
        }
    });
    stage.addEventListener('pointerdown', (e) => {
        if (focusOpen || grid || (e.pointerType === 'mouse' && e.button !== 0)) return;
        drag = { id: e.pointerId, x: e.clientX, y: e.clientY, startGoal: goal, trail: [{ x: e.clientX, t: performance.now() }], dragging: false };
    });
    stage.addEventListener('pointerup', releaseDrag);
    stage.addEventListener('pointercancel', releaseDrag);
    stage.addEventListener('pointerleave', () => {
        follow.classList.remove('is-on', 'is-view', 'is-link');
        fVisible = false;
    });
    stage.addEventListener('click', (e) => {
        if (performance.now() - dragEnd < 60) return;
        const hit = e.target.closest('.model__hit');
        if (!hit) return;
        const i = Number(hit.dataset.i);
        if (grid) {
            stopAuto();
            goal = pos = i;
            vel = 0;
            setActive(i);
            openFocus();
        } else {
            const d = shortest(i, Math.round(goal));
            if (d === 0) openFocus();
            else {
                stopAuto();
                goTo(Math.round(goal) + d);
            }
        }
    });
    stage.addEventListener('wheel', (e) => {
        if (focusOpen || grid || Math.abs(e.deltaX) <= Math.abs(e.deltaY)) return;
        e.preventDefault();
        if (wheelLock) return;
        wheelSum += e.deltaX;
        if (Math.abs(wheelSum) > 45) {
            stopAuto();
            if (wheelSum > 0) next(); else prev();
            wheelSum = 0;
            wheelLock = true;
            setTimeout(() => { wheelLock = false; }, 520);
        }
    }, { passive: false });
    stage.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight' || e.key === 'ArrowLeft' || (grid && (e.key === 'ArrowDown' || e.key === 'ArrowUp'))) {
            e.preventDefault();
            stopAuto();
            if (e.key === 'ArrowRight') next();
            else if (e.key === 'ArrowLeft') prev();
            else jumpTo(active + (e.key === 'ArrowDown' ? 3 : -3));
        } else if (e.key === 'Enter' && !focusOpen) {
            e.preventDefault();
            if (grid) {
                goal = pos = active;
                vel = 0;
            }
            openFocus();
        }
    });
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        if (focusOpen) closeFocus();
        else if (drawerOpen) setDrawer(false);
    });

    $('auto').addEventListener('click', () => setAutoplay(!autoplay));
    $('shop').addEventListener('click', openFocus);
    $('pieces').addEventListener('click', (e) => {
        const b = e.target.closest('.piece');
        if (!b) return;
        openFocus();
        setHot(Number(b.dataset.p), true);
    });
    $('drawerBtn').addEventListener('click', () => setDrawer(!drawerOpen));
    $('drawerClose').addEventListener('click', () => setDrawer(false));
    $('drawerList').addEventListener('click', (e) => {
        const b = e.target.closest('.drawer__item');
        if (!b) return;
        jumpTo(Number(b.dataset.i));
        setDrawer(false);
    });
    root.querySelectorAll('.deck__vbtn').forEach((b) => b.addEventListener('click', () => setGrid(b.dataset.v === 'grid')));
    $('focusClose').addEventListener('click', closeFocus);
    focusStage.addEventListener('click', (e) => {
        if (e.target.closest('.tag')) {
            stopAuto();
            return; // the dot is a link to the service
        }
        if (!e.target.closest('.model__fig')) closeFocus();
    });
    focusStage.addEventListener('pointerover', (e) => {
        const tag = e.target.closest('.tag');
        if (tag) setHot(Number(tag.dataset.p));
    });
    focusStage.addEventListener('focusin', (e) => {
        const tag = e.target.closest('.tag');
        if (tag) setHot(Number(tag.dataset.p));
    });
    lines.addEventListener('pointerover', (e) => {
        const row = e.target.closest('.line');
        if (row) setHot(Number(row.dataset.p));
    });
    lines.addEventListener('pointerleave', () => setHot(0));
    slip.addEventListener('pointerenter', (e) => { if (e.pointerType === 'mouse') slipHover = true; });
    slip.addEventListener('pointerleave', () => {
        slipHover = false;
        scheduleAuto();
    });

    if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) deck.classList.add('has-follow');
    else follow.remove();

    new ResizeObserver(measure).observe(stage);
    new IntersectionObserver((entries) => {
        visible = entries[entries.length - 1].isIntersecting;
        if (visible) {
            last = 0;
            kick();
        }
    }).observe(stage);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            last = 0;
            kick();
            scheduleAuto();
        }
    });
    $('nowTotal').textContent = pad(N);
    measure();
    setActive(0, true);
    setAutoplay(autoplay);
    kick();
}
