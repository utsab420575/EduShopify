/*
 * Edushopify Frontend V2 — vanilla JS only (per spec: no Alpine, no Livewire).
 * Grows per phase as each static page's own <script> block is ported over.
 * Independent of resources/js/frontend.js (legacy).
 *
 * Functions invoked from inline onclick="" attributes in Blade views are
 * assigned to `window` explicitly. Without that, Vite/Rollup treats them as
 * unused module-scope declarations (it can't see references inside HTML
 * attribute strings) and tree-shakes them out of the production bundle —
 * silently breaking every onclick that calls them.
 */

function fnOpenMobileMenu() {
    document.getElementById('fn-mobile-menu')?.classList.remove('translate-x-full');
    document.getElementById('fn-mobile-overlay')?.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function fnCloseMobileMenu() {
    document.getElementById('fn-mobile-menu')?.classList.add('translate-x-full');
    document.getElementById('fn-mobile-overlay')?.classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') fnCloseMobileMenu();
});

/* Product detail — gallery thumbnail switcher */
function switchImg(thumb, src) {
    const mainImg = document.getElementById('main-img');
    if (mainImg) mainImg.src = src;
    document.querySelectorAll('.thumb').forEach(function (t) { t.classList.remove('active'); });
    thumb.classList.add('active');
}

/* Supplier profile — section tab switcher */
function switchTab(btn, name) {
    document.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('active'); });
    document.querySelectorAll('.tab-panel').forEach(function (p) { p.style.display = 'none'; });
    btn.classList.add('active');
    const panel = document.getElementById('tab-' + name);
    if (panel) {
        if (name === 'about' || name === 'certifications' || name === 'contact') {
            panel.style.display = 'flex';
            panel.style.flexDirection = 'column';
            panel.style.gap = '16px';
        } else {
            panel.style.display = 'block';
        }
    }
}

window.fnOpenMobileMenu = fnOpenMobileMenu;
window.fnCloseMobileMenu = fnCloseMobileMenu;
window.switchImg = switchImg;
window.switchTab = switchTab;

/* RFQ list — live client-side search filter over the current page's cards */
const rfqSearchInput = document.getElementById('rfq-search');
if (rfqSearchInput) {
    rfqSearchInput.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        const cards = document.querySelectorAll('#rfq-list .rfq-card');
        let visible = 0;
        cards.forEach(function (card) {
            const match = card.textContent.toLowerCase().includes(q);
            card.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        const empty = document.getElementById('empty-state');
        if (empty) empty.classList.toggle('hidden', visible > 0);
    });
}

/* RFQ detail — deadline countdown */
(function () {
    const el = document.getElementById('deadline-countdown');
    if (!el || !el.dataset.deadline) return;
    const diff = Math.ceil((new Date(el.dataset.deadline) - new Date()) / (1000 * 60 * 60 * 24));
    if (diff > 0) {
        el.textContent = diff + (diff === 1 ? ' day remaining' : ' days remaining');
        el.className = 'text-xs text-red-500 mt-0.5';
    } else if (diff === 0) {
        el.textContent = 'Closes today';
        el.className = 'text-xs text-red-600 font-semibold mt-0.5';
    } else {
        el.textContent = 'Deadline passed';
        el.className = 'text-xs text-gray-400 mt-0.5';
    }
})();

/* RFQ detail — share button (Web Share API with clipboard fallback) */
function fnShare() {
    const data = { title: document.title, url: window.location.href };
    if (navigator.share) {
        navigator.share(data).catch(function () {});
    } else if (navigator.clipboard) {
        navigator.clipboard.writeText(window.location.href).then(function () {
            const t = document.createElement('div');
            t.textContent = 'Link copied to clipboard';
            t.className = 'fixed bottom-5 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-sm px-4 py-2 rounded-md shadow-lg z-50 transition-opacity';
            document.body.appendChild(t);
            setTimeout(function () { t.style.opacity = '0'; setTimeout(function () { t.remove(); }, 300); }, 2500);
        });
    }
}
window.fnShare = fnShare;

/*
 * Homepage — skeleton loading screen, then fade-up reveal.
 * No-ops on any page without #page-skeleton, so this is safe to run
 * everywhere frontend_new.js loads.
 */
(function () {
    const skeleton = document.getElementById('page-skeleton');
    const content = document.getElementById('page-content');
    if (!skeleton || !content) return;

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const MIN_SKELETON_MS = reducedMotion ? 0 : 500;
    const started = Date.now();

    function reveal() {
        const elapsed = Date.now() - started;
        const wait = Math.max(0, MIN_SKELETON_MS - elapsed);

        setTimeout(function () {
            skeleton.style.display = 'none';
            content.classList.remove('home-content-hidden');

            const sections = content.querySelectorAll('.reveal-up');
            sections.forEach(function (section, i) {
                setTimeout(function () { section.classList.add('is-visible'); }, reducedMotion ? 0 : i * 120);
            });
        }, wait);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', reveal);
    } else {
        reveal();
    }
})();

/*
 * Homepage — All Suppliers category tabs (spec §23.4 pattern).
 * Guarded on .supplier-item so this doesn't also attach to the supplier
 * profile page's differently-purposed .tab-btn section tabs (same class
 * name, different widget, and this script loads on every page).
 */
if (document.querySelector('.supplier-item')) {
    document.querySelectorAll('.tab-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.tab-btn').forEach(function (b) {
                b.classList.remove('tag-active');
                b.classList.add('tag-inactive');
            });
            this.classList.remove('tag-inactive');
            this.classList.add('tag-active');

            const cat = this.dataset.cat;
            document.querySelectorAll('.supplier-item').forEach(function (item) {
                item.style.display = (cat === 'all' || item.dataset.cat === cat) ? '' : 'none';
            });
        });
    });
}
