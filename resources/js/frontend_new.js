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
