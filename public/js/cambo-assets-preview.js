const feedback = document.querySelector('#feedback');
const title = value => value.replaceAll('-', ' ').replace(/\b\w/g, char => char.toUpperCase());
const el = (tag, text, className) => { const node = document.createElement(tag); if (text) node.textContent = text; if (className) node.className = className; return node; };
function image(path, name) { const node = el('img'); node.src = path; node.alt = name; node.loading = 'lazy'; return node; }
function link(path, label) { const node = el('a', label); node.href = path; return node; }
function copyButton(path) { const button = el('button', 'Copy path'); button.type = 'button'; button.addEventListener('click', async () => { try { await navigator.clipboard.writeText(path); feedback.textContent = 'Copied: ' + path; } catch (_) { feedback.textContent = 'Select the path displayed on the card to copy it.'; } }); return button; }
function card(asset, kind = '') {
    const node = el('article', '', 'asset-card ch-hover'); node.dataset.search = asset.name;
    const preview = el('div', '', 'preview ' + kind); preview.append(image(asset.path, title(asset.name)));
    const body = el('div', '', 'card-body'); body.append(el('h3', title(asset.name)), el('code', asset.path, 'asset-path'));
    const actions = el('div', '', 'card-actions'); actions.append(link(asset.path, 'Open image ↗'), copyButton(asset.path)); body.append(actions); node.append(preview, body); return node;
}
document.querySelector('#theme').addEventListener('click', event => { const dark = document.documentElement.dataset.theme !== 'dark'; document.documentElement.dataset.theme = dark ? 'dark' : 'light'; event.currentTarget.setAttribute('aria-pressed', String(dark)); event.currentTarget.textContent = dark ? 'Light preview' : 'Dark preview'; });
document.querySelector('#filter').addEventListener('input', event => { const query = event.target.value.trim().toLowerCase(); document.querySelectorAll('[data-search]').forEach(node => { node.hidden = !node.dataset.search.toLowerCase().includes(query); }); });
document.querySelector('#replay').addEventListener('click', () => { const target = document.querySelector('#entrance'); target.classList.remove('ch-enter'); requestAnimationFrame(() => requestAnimationFrame(() => target.classList.add('ch-enter'))); });
async function load() {
    const response = await fetch('cambo-assets.json'); if (!response.ok) throw new Error('Unable to load asset manifest');
    const manifest = await response.json();
    for (const [name, color] of Object.entries(manifest.palette)) { const node = el('div'); const swatch = el('div', '', 'swatch'); swatch.style.background = color; node.append(swatch, el('strong', title(name)), el('code', color)); document.querySelector('#palette').append(node); }
    for (const service of manifest.services) {
        const node = card({ name: service.name, path: service.background }); const body = node.querySelector('.card-body'); const links = el('div', '', 'service-links');
        links.append(link(service.icon, 'Default icon ↗'), link(service.activeIcon, 'Accent icon ↗'));
        if (service.illustration) links.append(link(service.illustration, 'Card illustration ↗')); body.append(links); document.querySelector('#service-grid').append(node);
    }
    for (const [items, target, kind] of [[manifest.backgrounds, '#background-grid', 'background'], [manifest.states, '#state-grid', ''], [manifest.placeholders, '#placeholder-grid', '']]) { for (const asset of items) document.querySelector(target).append(card(asset, kind)); }
    for (const icon of manifest.icons) { const node = el('article', '', 'icon-card'); node.dataset.search = icon.name; node.append(image(icon.path, title(icon.name)), el('strong', title(icon.name)), copyButton(icon.path)); document.querySelector('#icon-grid').append(node); }
    for (const animation of manifest.animations) {
        const node = el('article', '', 'asset-card'); node.dataset.search = animation.name;
        const stage = el('div', '', 'animation-stage'); const button = el('button', 'Play animation'); button.type = 'button'; let playing = false;
        const video = document.createElement('video');
        video.controls = true; video.playsInline = true; video.muted = true; video.preload = 'none';
        video.src = animation.path; video.poster = animation.poster;
        video.setAttribute('aria-label', animation.name);
        stage.append(video);
        const syncPlayback = () => { playing = !video.paused && !video.ended; button.textContent = playing ? 'Pause video' : 'Play video'; button.setAttribute('aria-pressed', String(playing)); };
        video.addEventListener('play', syncPlayback); video.addEventListener('pause', syncPlayback); video.addEventListener('ended', syncPlayback);
        button.textContent = 'Play video'; button.setAttribute('aria-pressed', 'false');
        button.addEventListener('click', async () => {
            if (!video.paused) { video.pause(); return; }
            if (video.ended) video.currentTime = 0;
            try { await video.play(); } catch (_) { feedback.textContent = 'Video could not play. Use Open video to download or view it.'; }
        });
        video.addEventListener('error', () => { feedback.textContent = 'Unable to load ' + animation.name + '. Open video directly to check the file.'; });
        const body = el('div', '', 'card-body'); const actions = el('div', '', 'card-actions'); actions.append(button, link(animation.path, 'Open video'), copyButton(animation.path)); body.append(el('h3', animation.name), el('p', animation.use), el('code', animation.path, 'asset-path'), actions); node.append(stage, body); document.querySelector('#animation-grid').append(node);
    }
}
load().catch(() => { feedback.textContent = 'Serve this folder with php artisan serve, then open /cambo-assets.html.'; });

