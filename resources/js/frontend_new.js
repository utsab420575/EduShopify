/*
 * Edushopify Frontend V2 — vanilla JS only (per spec: no Alpine, no Livewire).
 * Grows per phase as each static page's own <script> block is ported over.
 * Independent of resources/js/frontend.js (legacy).
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

/* Homepage — All Suppliers category tabs (spec §23.4 pattern) */
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
